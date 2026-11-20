<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\PacientePacote;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Venda;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $periodo = $request->get('periodo', 'hoje'); // hoje, semana, mes

        $dataHoje = Carbon::today();
        
        [$dataInicio, $dataFim] = match ($periodo) {
            'semana' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mes' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [$dataHoje->copy()->startOfDay(), $dataHoje->copy()->endOfDay()],
        };

        // 1. Atendimentos de hoje
        $agendamentosHojeQuery = Agendamento::with(['paciente', 'profissional', 'sala', 'servico'])
            ->whereDate('data_hora_inicio', $dataHoje)
            ->orderBy('data_hora_inicio', 'asc');

        if ($user->isFisioterapeuta()) {
            $agendamentosHojeQuery->where('profissional_id', $user->id);
        }

        $agendamentosHoje = $agendamentosHojeQuery->get();

        // Contadores de Agendamentos no Período Selecionado
        $agendamentosPeriodoQuery = Agendamento::whereBetween('data_hora_inicio', [$dataInicio, $dataFim]);
        if ($user->isFisioterapeuta()) {
            $agendamentosPeriodoQuery->where('profissional_id', $user->id);
        }

        $totalAgendamentos = (clone $agendamentosPeriodoQuery)->count();
        $confirmadosCount = (clone $agendamentosPeriodoQuery)->where('status', 'confirmado')->count();
        $concluidosCount = (clone $agendamentosPeriodoQuery)->where('status', 'concluido')->count();
        $faltasCount = (clone $agendamentosPeriodoQuery)->where('status', 'falta')->count();
        $canceladosCount = (clone $agendamentosPeriodoQuery)->where('status', 'cancelado')->count();

        // 2. Pacientes Ativos
        $totalPacientes = Paciente::where('status', 'ativo')->count();

        // 3. Pacotes em Alerta (Poucas sessões <= 2 ou vencendo nos próximos 15 dias)
        $pacotesAlerta = PacientePacote::with(['paciente', 'servico'])
            ->where('status', 'ativo')
            ->where(function ($q) {
                $q->where('sessoes_restantes', '<=', 2)
                  ->orWhereBetween('data_validade', [Carbon::today(), Carbon::today()->addDays(15)]);
            })
            ->take(5)
            ->get();

        // 4. Indicadores Financeiros (Apenas para Proprietária e Financeiro)
        $receitasRecebidas = 0;
        $receitasPendentes = 0;
        $despesasPagas = 0;
        $despesasPendentes = 0;

        if ($user->isProprietaria() || $user->isFinanceiro()) {
            $receitasRecebidas = ContaReceber::where('status', 'pago')
                ->whereBetween('data_recebimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
                ->sum('valor_nominal');

            $receitasPendentes = ContaReceber::where('status', 'pendente')
                ->whereBetween('data_vencimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
                ->sum('valor_nominal');

            $despesasPagas = ContaPagar::where('status', 'pago')
                ->whereBetween('data_pagamento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
                ->sum('valor');

            $despesasPendentes = ContaPagar::where('status', 'pendente')
                ->whereBetween('data_vencimento', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
                ->sum('valor');
        }

        return view('dashboard.index', compact(
            'periodo',
            'agendamentosHoje',
            'totalAgendamentos',
            'confirmadosCount',
            'concluidosCount',
            'faltasCount',
            'canceladosCount',
            'totalPacientes',
            'pacotesAlerta',
            'receitasRecebidas',
            'receitasPendentes',
            'despesasPagas',
            'despesasPendentes'
        ));
    }
}
