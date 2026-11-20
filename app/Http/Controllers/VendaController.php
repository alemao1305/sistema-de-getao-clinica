<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Venda;
use App\Models\Paciente;
use App\Models\Pacote;
use App\Models\Servico;
use App\Models\PacientePacote;
use App\Models\FormaPagamento;
use App\Models\ContaReceber;
use App\Models\CategoriaFinanceira;
use App\Services\AuditService;
use Carbon\Carbon;

class VendaController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->get('busca');
        $status = $request->get('status');

        $vendas = Venda::with(['paciente', 'vendedor', 'itens', 'pagamentos'])
            ->when($busca, function ($q) use ($busca) {
                $q->where('codigo', 'like', "%{$busca}%")
                  ->orWhereHas('paciente', fn($p) => $p->where('nome', 'like', "%{$busca}%"));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('data_venda', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vendas.index', compact('vendas', 'busca', 'status'));
    }

    public function create(Request $request)
    {
        $pacienteId = $request->get('paciente_id');
        $pacientes = Paciente::where('status', 'ativo')->orderBy('nome')->get();
        $pacotes = Pacote::with('servico')->where('ativo', true)->orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        $formasPagamento = FormaPagamento::where('ativo', true)->get();

        return view('vendas.create', compact('pacienteId', 'pacientes', 'pacotes', 'servicos', 'formasPagamento'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'tipo_item' => ['required', 'in:pacote,servico'],
            'item_id' => ['required', 'integer'],
            'valor_subtotal' => ['required', 'numeric', 'min:0'],
            'valor_desconto' => ['nullable', 'numeric', 'min:0'],
            'sessoes_bonus' => ['nullable', 'integer', 'min:0'],
            'data_venda' => ['required', 'date'],
            'observacoes' => ['nullable', 'string'],
            // Pagamentos (Split Payment)
            'pagamentos' => ['required', 'array', 'min:1'],
            'pagamentos.*.forma' => ['required', 'string'],
            'pagamentos.*.valor' => ['required', 'numeric', 'min:0.01'],
            'pagamentos.*.parcelas' => ['nullable', 'integer', 'min:1', 'max:12'],
        ], [
            'paciente_id.required' => 'Selecione o paciente comprador.',
            'pagamentos.required' => 'Adicione ao menos uma forma de pagamento.',
        ]);

        $desconto = (float) ($validated['valor_desconto'] ?? 0);
        $subtotal = (float) $validated['valor_subtotal'];
        $totalVenda = max(0, $subtotal - $desconto);

        // Validação da soma dos pagamentos
        $somaPagamentos = 0;
        foreach ($validated['pagamentos'] as $pag) {
            $somaPagamentos += (float) $pag['valor'];
        }

        if (abs($somaPagamentos - $totalVenda) > 0.05) {
            return back()->withInput()->with('error', "A soma das formas de pagamento (R$ " . number_format($somaPagamentos, 2, ',', '.') . ") deve ser exatamente igual ao valor total da venda (R$ " . number_format($totalVenda, 2, ',', '.') . ").");
        }

        $venda = DB::transaction(function () use ($validated, $subtotal, $desconto, $totalVenda, $request) {
            $user = Auth::user();
            $codigo = Venda::gerarCodigo();

            // 1. Cria a Venda
            $venda = Venda::create([
                'codigo' => $codigo,
                'paciente_id' => $validated['paciente_id'],
                'user_id' => $user->id,
                'data_venda' => $validated['data_venda'],
                'valor_subtotal' => $subtotal,
                'valor_desconto' => $desconto,
                'valor_total' => $totalVenda,
                'status' => 'pago',
                'observacoes' => $validated['observacoes'] ?? null,
            ]);

            // 2. Cria o Item da Venda
            $descricaoItem = '';
            $servicoId = null;
            $qtdSessoes = 1;
            $validadeDias = 30;

            if ($validated['tipo_item'] === 'pacote') {
                $pacote = Pacote::with('servico')->findOrFail($validated['item_id']);
                $descricaoItem = $pacote->nome;
                $servicoId = $pacote->servico_id;
                $qtdSessoes = $pacote->quantidade_sessoes;
                $validadeDias = $pacote->validade_dias;
            } else {
                $servico = Servico::findOrFail($validated['item_id']);
                $descricaoItem = $servico->nome;
                $servicoId = $servico->id;
                $qtdSessoes = 1;
                $validadeDias = 30;
            }

            $venda->itens()->create([
                'tipo_item' => $validated['tipo_item'],
                'item_id' => $validated['item_id'],
                'descricao' => $descricaoItem,
                'quantidade' => 1,
                'valor_unitario' => $subtotal,
                'valor_total' => $subtotal,
            ]);

            // 3. Registra os Pagamentos (Split Payment) & Gera Contas a Receber
            $categoriaReceita = CategoriaFinanceira::where('tipo', 'receita')
                ->where('nome', 'like', '%Pacote%')
                ->first() ?? CategoriaFinanceira::where('tipo', 'receita')->first();

            foreach ($validated['pagamentos'] as $pag) {
                $vendaPagamento = $venda->pagamentos()->create([
                    'forma_pagamento' => $pag['forma'],
                    'valor' => $pag['valor'],
                    'parcelas' => $pag['parcelas'] ?? 1,
                    'status' => 'pago',
                    'data_pagamento' => $validated['data_venda'],
                    'data_vencimento' => $validated['data_venda'],
                ]);

                // Lança no financeiro (Contas a Receber)
                ContaReceber::create([
                    'venda_id' => $venda->id,
                    'paciente_id' => $validated['paciente_id'],
                    'categoria_id' => $categoriaReceita?->id,
                    'descricao' => "Venda {$venda->codigo} - {$descricaoItem} (" . ucfirst($pag['forma']) . ")",
                    'valor_nominal' => $pag['valor'],
                    'valor_recebido' => $pag['valor'],
                    'data_vencimento' => $validated['data_venda'],
                    'data_recebimento' => $validated['data_venda'],
                    'status' => 'pago',
                ]);
            }

            // 4. Se for pacote, gera saldo de sessões para o paciente
            if ($validated['tipo_item'] === 'pacote') {
                $bonus = (int) ($validated['sessoes_bonus'] ?? 0);
                $totalSessoes = $qtdSessoes + $bonus;
                $dataInicio = Carbon::parse($validated['data_venda']);
                $dataValidade = $dataInicio->copy()->addDays($validadeDias);

                $pacotePaciente = PacientePacote::create([
                    'paciente_id' => $validated['paciente_id'],
                    'venda_id' => $venda->id,
                    'pacote_id' => $validated['item_id'],
                    'servico_id' => $servicoId,
                    'nome_pacote' => $descricaoItem,
                    'sessoes_totais' => $totalSessoes,
                    'sessoes_utilizadas' => 0,
                    'sessoes_restantes' => $totalSessoes,
                    'sessoes_bonus' => $bonus,
                    'data_inicio' => $dataInicio,
                    'data_validade' => $dataValidade,
                    'status' => 'ativo',
                    'observacoes' => "Adquirido na venda {$venda->codigo}",
                ]);

                // Registra histórico inicial de crédito de sessões
                $pacotePaciente->consumos()->create([
                    'user_id' => $user->id,
                    'tipo' => 'ajuste_manual',
                    'quantidade' => $totalSessoes,
                    'motivo' => "Crédito inicial da venda {$venda->codigo}" . ($bonus > 0 ? " (inclui {$bonus} sessão(ões) bônus)" : ""),
                ]);
            }

            AuditService::log('vendas.create', "Realizou a venda {$venda->codigo} no valor de R$ " . number_format($totalVenda, 2, ',', '.') . " para o paciente ID {$validated['paciente_id']}", $venda);

            return $venda;
        });

        return redirect()->route('vendas.show', $venda->id)
            ->with('success', "Venda {$venda->codigo} confirmada e saldo de sessões gerado com sucesso!");
    }

    public function show($id)
    {
        $venda = Venda::with(['paciente', 'vendedor', 'itens', 'pagamentos', 'pacotesGerados'])->findOrFail($id);
        return view('vendas.show', compact('venda'));
    }
}
