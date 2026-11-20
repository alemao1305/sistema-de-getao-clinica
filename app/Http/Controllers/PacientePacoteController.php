<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PacientePacote;
use App\Models\Paciente;
use App\Services\AuditService;

class PacientePacoteController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $busca = $request->get('busca');

        $pacotes = PacientePacote::with(['paciente', 'servico', 'venda'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($busca, function ($q) use ($busca) {
                $q->where('nome_pacote', 'like', "%{$busca}%")
                  ->orWhereHas('paciente', fn($p) => $p->where('nome', 'like', "%{$busca}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vendas.pacotes-saldos', compact('pacotes', 'status', 'busca'));
    }

    public function show($id)
    {
        $pacote = PacientePacote::with([
            'paciente',
            'servico',
            'venda',
            'consumos' => fn($q) => $q->with(['agendamento', 'responsavel'])->orderBy('created_at', 'desc'),
        ])->findOrFail($id);

        return view('vendas.pacote-extrato', compact('pacote'));
    }

    public function ajustarSaldo(Request $request, $id)
    {
        $pacote = PacientePacote::findOrFail($id);

        $validated = $request->validate([
            'tipo_ajuste' => ['required', 'in:bonus,adicionar,reduzir,alterar_validade,pausar,ativar'],
            'quantidade' => ['nullable', 'integer', 'min:1'],
            'nova_validade' => ['nullable', 'date'],
            'motivo' => ['required', 'string', 'min:3'],
        ], [
            'motivo.required' => 'Informe a justificativa/motivo do ajuste de saldo.',
        ]);

        $user = Auth::user();
        $motivo = $validated['motivo'];
        $tipo = $validated['tipo_ajuste'];

        if ($tipo === 'bonus' || $tipo === 'adicionar') {
            $qtd = (int) $validated['quantidade'];
            $pacote->sessoes_totais += $qtd;
            $pacote->sessoes_restantes += $qtd;
            if ($tipo === 'bonus') {
                $pacote->sessoes_bonus += $qtd;
            }
            if ($pacote->status === 'concluido') {
                $pacote->status = 'ativo';
            }
            $pacote->save();

            $pacote->consumos()->create([
                'user_id' => $user->id,
                'tipo' => 'ajuste_manual',
                'quantidade' => $qtd,
                'motivo' => "Acréscimo de {$qtd} sessão(ões): {$motivo}",
            ]);
        } elseif ($tipo === 'reduzir') {
            $qtd = (int) $validated['quantidade'];
            $pacote->sessoes_restantes = max(0, $pacote->sessoes_restantes - $qtd);
            $pacote->sessoes_utilizadas += $qtd;
            if ($pacote->sessoes_restantes <= 0) {
                $pacote->status = 'concluido';
            }
            $pacote->save();

            $pacote->consumos()->create([
                'user_id' => $user->id,
                'tipo' => 'ajuste_manual',
                'quantidade' => -$qtd,
                'motivo' => "Redução manual de {$qtd} sessão(ões): {$motivo}",
            ]);
        } elseif ($tipo === 'alterar_validade') {
            $antiga = $pacote->data_validade->format('d/m/Y');
            $pacote->data_validade = $validated['nova_validade'];
            $pacote->save();

            $pacote->consumos()->create([
                'user_id' => $user->id,
                'tipo' => 'ajuste_manual',
                'quantidade' => 0,
                'motivo' => "Validade prorrogada de {$antiga} para {$pacote->data_validade->format('d/m/Y')}: {$motivo}",
            ]);
        } elseif ($tipo === 'pausar') {
            $pacote->status = 'pausado';
            $pacote->save();
            $pacote->consumos()->create([
                'user_id' => $user->id,
                'tipo' => 'ajuste_manual',
                'quantidade' => 0,
                'motivo' => "Pacote pausado temporariamente: {$motivo}",
            ]);
        } elseif ($tipo === 'ativar') {
            $pacote->status = 'ativo';
            $pacote->save();
            $pacote->consumos()->create([
                'user_id' => $user->id,
                'tipo' => 'ajuste_manual',
                'quantidade' => 0,
                'motivo' => "Pacote reativado: {$motivo}",
            ]);
        }

        AuditService::log('pacotes.ajuste', "Ajuste de saldo no pacote #{$pacote->id} ({$tipo}): {$motivo}", $pacote);

        return back()->with('success', 'Ajuste de saldo realizado e registrado no extrato do pacote.');
    }
}
