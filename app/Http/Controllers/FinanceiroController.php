<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\RepasseProfissional;
use App\Models\CategoriaFinanceira;
use App\Models\FormaPagamento;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // mes, semana, hoje, custom
        $mesAno = $request->get('mes_ano', now()->format('Y-m'));

        $dataInicio = Carbon::parse("{$mesAno}-01")->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        // 1. Receitas
        $receitasRealizadas = ContaReceber::where('status', 'pago')
            ->whereBetween('data_recebimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor_recebido');

        $receitasPrevistas = ContaReceber::where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor_nominal');

        // 2. Despesas
        $despesasRealizadas = ContaPagar::where('status', 'pago')
            ->whereBetween('data_pagamento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor_pago');

        $despesasPrevistas = ContaPagar::where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor');

        // 3. Repasses Pagos a Profissionais
        $repassesPagos = RepasseProfissional::where('status', 'pago')
            ->whereBetween('data_pagamento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor_repasse');

        $repassesPendentes = RepasseProfissional::where('status', 'pendente')
            ->whereBetween('data_competencia', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->sum('valor_repasse');

        // Saldo Operacional Líquido do Mês
        $saldoRealizado = $receitasRealizadas - ($despesasRealizadas + $repassesPagos);

        // Últimas Movimentações Mistas (Extrato)
        $ultimosRecebimentos = ContaReceber::with(['paciente', 'categoria'])
            ->where('status', 'pago')
            ->orderBy('data_recebimento', 'desc')
            ->take(8)
            ->get();

        $ultimosPagamentos = ContaPagar::with('categoria')
            ->where('status', 'pago')
            ->orderBy('data_pagamento', 'desc')
            ->take(8)
            ->get();

        return view('financeiro.index', compact(
            'mesAno',
            'receitasRealizadas',
            'receitasPrevistas',
            'despesasRealizadas',
            'despesasPrevistas',
            'repassesPagos',
            'repassesPendentes',
            'saldoRealizado',
            'ultimosRecebimentos',
            'ultimosPagamentos'
        ));
    }

    public function receber(Request $request)
    {
        $status = $request->get('status');
        $busca = $request->get('busca');

        $contas = ContaReceber::with(['paciente', 'categoria', 'formaPagamento', 'venda'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($busca, function ($q) use ($busca) {
                $q->where('descricao', 'like', "%{$busca}%")
                  ->orWhereHas('paciente', fn($p) => $p->where('nome', 'like', "%{$busca}%"));
            })
            ->orderBy('data_vencimento', 'desc')
            ->paginate(15)
            ->withQueryString();

        $formasPagamento = FormaPagamento::where('ativo', true)->get();

        return view('financeiro.receber', compact('contas', 'status', 'busca', 'formasPagamento'));
    }

    public function baixarReceber(Request $request, $id)
    {
        $conta = ContaReceber::findOrFail($id);

        $validated = $request->validate([
            'valor_recebido' => ['required', 'numeric', 'min:0.01'],
            'data_recebimento' => ['required', 'date'],
            'forma_pagamento_id' => ['nullable', 'exists:formas_pagamento,id'],
        ]);

        $conta->valor_recebido = $validated['valor_recebido'];
        $conta->data_recebimento = $validated['data_recebimento'];
        if ($request->filled('forma_pagamento_id')) {
            $conta->forma_pagamento_id = $validated['forma_pagamento_id'];
        }
        $conta->status = 'pago';
        $conta->save();

        AuditService::log('financeiro.receber.baixa', "Baixou recebimento de R$ {$conta->valor_recebido} referente a '{$conta->descricao}'", $conta);

        return back()->with('success', 'Recebimento confirmado com sucesso!');
    }

    public function pagar(Request $request)
    {
        $status = $request->get('status');
        $busca = $request->get('busca');

        $contas = ContaPagar::with('categoria')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($busca, function ($q) use ($busca) {
                $q->where('descricao', 'like', "%{$busca}%")
                  ->orWhere('fornecedor', 'like', "%{$busca}%");
            })
            ->orderBy('data_vencimento', 'asc')
            ->paginate(15)
            ->withQueryString();

        $categorias = CategoriaFinanceira::where('tipo', 'despesa')->get();

        return view('financeiro.pagar', compact('contas', 'status', 'busca', 'categorias'));
    }

    public function storePagar(Request $request)
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'fornecedor' => ['nullable', 'string', 'max:255'],
            'categoria_id' => ['nullable', 'exists:categorias_financeiras,id'],
            'centro_custo' => ['nullable', 'string', 'max:100'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_vencimento' => ['required', 'date'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $conta = ContaPagar::create([
            'descricao' => $validated['descricao'],
            'fornecedor' => $validated['fornecedor'] ?? null,
            'categoria_id' => $validated['categoria_id'] ?? null,
            'centro_custo' => $validated['centro_custo'] ?? 'Geral',
            'valor' => $validated['valor'],
            'data_vencimento' => $validated['data_vencimento'],
            'status' => 'pendente',
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        AuditService::log('financeiro.pagar.create', "Cadastrou conta a pagar: {$conta->descricao} no valor de R$ {$conta->valor}", $conta);

        return back()->with('success', 'Conta a pagar lançada com sucesso!');
    }

    public function baixarPagar(Request $request, $id)
    {
        $conta = ContaPagar::findOrFail($id);

        $validated = $request->validate([
            'valor_pago' => ['required', 'numeric', 'min:0.01'],
            'data_pagamento' => ['required', 'date'],
        ]);

        $conta->valor_pago = $validated['valor_pago'];
        $conta->data_pagamento = $validated['data_pagamento'];
        $conta->status = 'pago';
        $conta->save();

        AuditService::log('financeiro.pagar.baixa', "Baixou despesa paga de R$ {$conta->valor_pago} referente a '{$conta->descricao}'", $conta);

        return back()->with('success', 'Pagamento de despesa confirmado!');
    }

    public function repasses(Request $request)
    {
        $profissionalId = $request->get('profissional_id');
        $status = $request->get('status');

        $repasses = RepasseProfissional::with(['profissional', 'agendamento.paciente', 'agendamento.servico'])
            ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('data_competencia', 'desc')
            ->paginate(15)
            ->withQueryString();

        $profissionais = User::whereIn('role', ['fisioterapeuta', 'proprietaria'])->where('is_active', true)->get();

        $totalPendente = RepasseProfissional::where('status', 'pendente')
            ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
            ->sum('valor_repasse');

        $totalPago = RepasseProfissional::where('status', 'pago')
            ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
            ->sum('valor_repasse');

        return view('financeiro.repasses', compact('repasses', 'profissionais', 'profissionalId', 'status', 'totalPendente', 'totalPago'));
    }

    public function pagarRepasse(Request $request, $id)
    {
        $repasse = RepasseProfissional::findOrFail($id);
        $repasse->status = 'pago';
        $repasse->data_pagamento = now();
        $repasse->save();

        AuditService::log('financeiro.repasse.pago', "Confirmou repasse profissional de R$ {$repasse->valor_repasse} para {$repasse->profissional->name}", $repasse);

        return back()->with('success', "Repasse de R$ " . number_format($repasse->valor_repasse, 2, ',', '.') . " liquidado com sucesso!");
    }
}
