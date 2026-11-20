@extends('layouts.app')

@section('title', 'Painel Geral')
@section('header', 'Painel de Controle')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar & Welcome -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                Olá, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Aqui está o panorama operacional e clínico da clínica.
            </p>
        </div>

        <!-- Period Selector -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-semibold self-start sm:self-auto">
            <a href="{{ route('dashboard', ['periodo' => 'hoje']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $periodo === 'hoje' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Hoje
            </a>
            <a href="{{ route('dashboard', ['periodo' => 'semana']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $periodo === 'semana' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Esta Semana
            </a>
            <a href="{{ route('dashboard', ['periodo' => 'mes']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $periodo === 'mes' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Este Mês
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Atendimentos -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Atendimentos</span>
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600"><i data-lucide="calendar" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $totalAgendamentos }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                <span class="text-emerald-600 font-semibold">{{ $concluidosCount }} concluídos</span>
                <span>&bull;</span>
                <span>{{ $confirmadosCount }} confirmados</span>
            </div>
        </div>

        <!-- Faltas e Cancelamentos -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Faltas / Cancelados</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i data-lucide="user-x" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $faltasCount + $canceladosCount }}</div>
            <div class="text-xs text-slate-500 mt-1">
                <span class="text-orange-600 font-semibold">{{ $faltasCount }} faltas</span> / 
                <span class="text-rose-600 font-semibold">{{ $canceladosCount }} cancelados</span>
            </div>
        </div>

        @if(auth()->user()->isProprietaria() || auth()->user()->isFinanceiro())
        <!-- Receitas Recebidas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Receitas Recebidas</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-emerald-700">R$ {{ number_format($receitasRecebidas, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">
                A receber: R$ {{ number_format($receitasPendentes, 2, ',', '.') }}
            </div>
        </div>

        <!-- Despesas Pagas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Despesas Pagas</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-rose-700">R$ {{ number_format($despesasPagas, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">
                A pagar: R$ {{ number_format($despesasPendentes, 2, ',', '.') }}
            </div>
        </div>
        @else
        <!-- Pacientes Ativos -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Pacientes Ativos</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600"><i data-lucide="users" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $totalPacientes }}</div>
            <div class="text-xs text-slate-500 mt-1">Cadastrados e em tratamento</div>
        </div>

        <!-- Alertas de Pacotes -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Alertas de Pacotes</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600"><i data-lucide="alert-circle" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-amber-700">{{ $pacotesAlerta->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Sessões baixas ou a vencer</div>
        </div>
        @endif
    </div>

    <!-- Main Grid: Today's Schedule & Quick Actions/Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Today's Schedule Timeline (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                    <h2 class="text-base font-bold text-slate-900">Atendimentos de Hoje</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                    {{ $agendamentosHoje->count() }} agendamento(s)
                </span>
            </div>

            <div class="p-5 flex-1 divide-y divide-slate-100">
                @forelse($agendamentosHoje as $agendamento)
                <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="text-center bg-teal-50 text-teal-800 rounded-xl p-2 min-w-[64px]">
                            <span class="block text-sm font-bold leading-none">{{ $agendamento->data_hora_inicio->format('H:i') }}</span>
                            <span class="block text-[10px] text-teal-600 mt-0.5">{{ $agendamento->duracao_minutos }} min</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pacientes.show', $agendamento->paciente_id) }}" class="text-sm font-bold text-slate-900 hover:text-teal-600 transition">
                                    {{ $agendamento->paciente->nome }}
                                </a>
                                @php $badge = $agendamento->status_badge; @endphp
                                <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $agendamento->servico->nome ?? 'Atendimento Fisioterapêutico' }} &bull;
                                <span class="font-medium text-slate-700">{{ $agendamento->profissional->name }}</span>
                                @if($agendamento->sala)
                                    &bull; <span class="text-teal-700 font-medium">{{ $agendamento->sala->nome }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        @if($agendamento->status !== 'concluido' && $agendamento->status !== 'cancelado')
                            <!-- Botão Iniciar / Concluir Atendimento -->
                            <a href="{{ route('agenda.atendimento', $agendamento->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold transition">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                <span>Atender</span>
                            </a>
                        @endif
                        <a href="{{ route('prontuarios.show', $agendamento->paciente_id) }}"
                           title="Acessar Prontuário"
                           class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400">
                    <i data-lucide="calendar-check" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                    <p class="text-sm font-medium">Nenhum atendimento agendado para hoje.</p>
                    <a href="{{ route('agenda.index', ['novo' => 1]) }}" class="mt-3 inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agendar novo atendimento
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Quick Actions & Package Alerts -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-teal-600"></i>
                    Ações Rápidas
                </h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('pacientes.create') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-800 flex flex-col gap-1 transition">
                        <i data-lucide="user-plus" class="w-5 h-5 text-teal-600"></i>
                        <span class="font-bold">Novo Paciente</span>
                    </a>
                    <a href="{{ route('agenda.index', ['novo' => 1]) }}" class="p-3 rounded-xl bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-800 flex flex-col gap-1 transition">
                        <i data-lucide="calendar-plus" class="w-5 h-5 text-teal-600"></i>
                        <span class="font-bold">Agendar Sessão</span>
                    </a>
                    @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->isFinanceiro())
                    <a href="{{ route('vendas.create') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-800 flex flex-col gap-1 transition">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-teal-600"></i>
                        <span class="font-bold">Vender Pacote</span>
                    </a>
                    <a href="{{ route('financeiro.pagar', ['novo' => 1]) }}" class="p-3 rounded-xl bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-800 flex flex-col gap-1 transition">
                        <i data-lucide="receipt" class="w-5 h-5 text-teal-600"></i>
                        <span class="font-bold">Lançar Despesa</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Pacotes Próximos do Vencimento ou Fim -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="alert-octagon" class="w-4 h-4 text-amber-500"></i>
                        Alerta de Pacotes
                    </span>
                    <a href="{{ route('pacientes-pacotes.index') }}" class="text-[11px] text-teal-600 font-semibold hover:underline">Ver todos</a>
                </h3>

                <div class="space-y-3">
                    @forelse($pacotesAlerta as $pacote)
                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-200/70 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <a href="{{ route('pacientes.show', $pacote->paciente_id) }}" class="hover:underline">
                                {{ $pacote->paciente->nome }}
                            </a>
                            <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-800 font-bold text-[10px]">
                                {{ $pacote->sessoes_restantes }} restante(s)
                            </span>
                        </div>
                        <p class="text-slate-600 mt-1 truncate">{{ $pacote->nome_pacote }}</p>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Vence em: {{ $pacote->data_validade->format('d/m/Y') }}</span>
                            @if($pacote->sessoes_restantes <= 1)
                                <a href="{{ route('vendas.create', ['paciente_id' => $pacote->paciente_id]) }}" class="font-bold text-teal-700 hover:underline">Renovar</a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-3 text-center">Nenhum pacote em situação de alerta no momento.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
