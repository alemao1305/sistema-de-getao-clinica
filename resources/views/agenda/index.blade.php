@extends('layouts.app')

@section('title', 'Agenda')
@section('header', 'Agenda de Atendimentos')

@section('content')
<div class="space-y-6" x-data="{
    modalNovo: {{ request()->get('novo') ? 'true' : 'false' }},
    modalStatus: false,
    modalRemarcar: false,
    modalBloqueio: false,
    agendamentoSelecionado: null,
    statusAlvo: 'concluido',
    motivoCancelamento: ''
}">
    <!-- Top Action & Navigation Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Date Navigation -->
        <div class="flex items-center gap-2">
            @php
                $subData = match($viewMode) {
                    'semana' => $dataRef->copy()->subWeek()->format('Y-m-d'),
                    'mes' => $dataRef->copy()->subMonth()->format('Y-m-d'),
                    default => $dataRef->copy()->subDay()->format('Y-m-d'),
                };
                $addData = match($viewMode) {
                    'semana' => $dataRef->copy()->addWeek()->format('Y-m-d'),
                    'mes' => $dataRef->copy()->addMonth()->format('Y-m-d'),
                    default => $dataRef->copy()->addDay()->format('Y-m-d'),
                };
            @endphp
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['data' => $subData])) }}"
               class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition" title="Anterior">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </a>
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['data' => now()->format('Y-m-d')])) }}"
               class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Hoje
            </a>
            <a href="{{ route('agenda.index', array_merge(request()->query(), ['data' => $addData])) }}"
               class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition" title="Próximo">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>

            <div class="ml-2">
                <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                    @if($viewMode === 'semana')
                        Semana de {{ $dataRef->copy()->startOfWeek()->format('d/m') }} a {{ $dataRef->copy()->endOfWeek()->format('d/m/Y') }}
                    @elseif($viewMode === 'mes')
                        {{ $dataRef->locale('pt_BR')->translatedFormat('F \d\e Y') }}
                    @else
                        {{ $dataRef->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}
                    @endif
                </h1>
            </div>
        </div>

        <!-- Controls: View Mode & New Schedule -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Mode Switcher -->
            <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                <a href="{{ route('agenda.index', array_merge(request()->query(), ['modo' => 'dia'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'dia' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Dia
                </a>
                <a href="{{ route('agenda.index', array_merge(request()->query(), ['modo' => 'semana'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'semana' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semana
                </a>
                <a href="{{ route('agenda.index', array_merge(request()->query(), ['modo' => 'lista'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'lista' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Lista
                </a>
            </div>

            <!-- Exportar iCal -->
            <a href="{{ route('agenda.ical', ['profissional_id' => $profissionalId]) }}"
               class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition" title="Exportar para Google Calendar / Apple Calendar (iCal)">
                <i data-lucide="download" class="w-4 h-4"></i>
            </a>

            <!-- Bloqueio de Horário -->
            <button @click="modalBloqueio = true"
                    class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i data-lucide="clock-4" class="w-4 h-4 text-amber-600"></i>
                <span class="hidden sm:inline">Bloquear Horário</span>
            </button>

            <!-- Configurar Salas -->
            @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('configuracoes.manage'))
            <a href="{{ route('salas.index') }}"
               class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition"
               title="Configurar Salas e Boxes de Atendimento">
                <i data-lucide="door-open" class="w-4 h-4 text-teal-600"></i>
                <span class="hidden sm:inline">Configurar Salas</span>
            </a>
            @endif

            <!-- Novo Agendamento -->
            <button @click="modalNovo = true"
                    class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Novo Agendamento</span>
            </button>
        </div>
    </div>

    <!-- Filter Strip -->
    <div class="bg-white p-3.5 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('agenda.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <input type="hidden" name="data" value="{{ $dataRef->format('Y-m-d') }}">
            <input type="hidden" name="modo" value="{{ $viewMode }}">

            <!-- Profissional Filter -->
            <div class="flex items-center gap-1.5">
                <label for="f_prof" class="font-semibold text-slate-500">Profissional:</label>
                <select id="f_prof" name="profissional_id" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 py-1.5 px-2.5 text-xs focus:ring-teal-600 font-medium">
                    <option value="">Todos os profissionais</option>
                    @foreach($profissionais as $p)
                        <option value="{{ $p->id }}" {{ (string)$profissionalId === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sala Filter -->
            <div class="flex items-center gap-1.5">
                <label for="f_sala" class="font-semibold text-slate-500">Sala:</label>
                <div class="flex items-center gap-1">
                    <select id="f_sala" name="sala_id" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-300 py-1.5 px-2.5 text-xs focus:ring-teal-600 font-medium">
                        <option value="">Todas as salas</option>
                        @foreach($salas as $s)
                            <option value="{{ $s->id }}" {{ (string)$salaId === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->nome }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('configuracoes.manage'))
                    <a href="{{ route('salas.index') }}" class="p-1.5 text-slate-400 hover:text-teal-600 hover:bg-slate-50 rounded-lg transition" title="Configurar e Gerenciar Salas">
                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-1.5">
                <label for="f_status" class="font-semibold text-slate-500">Situação:</label>
                <select id="f_status" name="status" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 py-1.5 px-2.5 text-xs focus:ring-teal-600 font-medium">
                    <option value="">Todas</option>
                    <option value="agendado" {{ $status === 'agendado' ? 'selected' : '' }}>Agendado</option>
                    <option value="confirmado" {{ $status === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="em_atendimento" {{ $status === 'em_atendimento' ? 'selected' : '' }}>Em Atendimento</option>
                    <option value="concluido" {{ $status === 'concluido' ? 'selected' : '' }}>Concluído</option>
                    <option value="falta" {{ $status === 'falta' ? 'selected' : '' }}>Falta</option>
                    <option value="cancelado" {{ $status === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            @if($profissionalId || $salaId || $status)
            <a href="{{ route('agenda.index', ['data' => $dataRef->format('Y-m-d'), 'modo' => $viewMode]) }}"
               class="text-teal-600 hover:underline font-semibold ml-auto">
                Limpar filtros
            </a>
            @endif
        </form>
    </div>

    <!-- Active Schedule Blocks Banner if any -->
    @if($bloqueios->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 text-xs text-amber-900 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <i data-lucide="lock" class="w-4 h-4 text-amber-600"></i>
            Bloqueios de Horário Ativos no Período:
        </div>
        <div class="flex flex-wrap gap-2 pt-1">
            @foreach($bloqueios as $bl)
            <div class="inline-flex items-center gap-2 bg-white px-3 py-1 rounded-lg border border-amber-300 shadow-2xs">
                <span class="font-semibold">{{ $bl->titulo }}</span>
                <span class="text-amber-700">({{ $bl->data_hora_inicio->format('d/m H:i') }} às {{ $bl->data_hora_fim->format('d/m H:i') }})</span>
                @if($bl->profissional) &bull; <span class="text-slate-600">{{ $bl->profissional->name }}</span> @endif
                @if($bl->sala) &bull; <span class="text-slate-600">{{ $bl->sala->nome }}</span> @endif
                <form method="POST" action="{{ route('agenda.bloqueios.destroy', $bl->id) }}" onsubmit="return confirm('Remover este bloqueio?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-0.5"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Calendar Representation -->
    @if($viewMode === 'lista')
        <!-- List View -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                        <th class="py-3.5 px-4">Horário</th>
                        <th class="py-3.5 px-4">Paciente</th>
                        <th class="py-3.5 px-4">Serviço</th>
                        <th class="py-3.5 px-4">Fisioterapeuta</th>
                        <th class="py-3.5 px-4">Sala</th>
                        <th class="py-3.5 px-4">Tipo</th>
                        <th class="py-3.5 px-4">Situação</th>
                        <th class="py-3.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agendamentos as $ag)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $ag->data_hora_inicio->format('d/m/Y H:i') }}
                            <span class="text-[10px] text-slate-400 block font-normal">{{ $ag->duracao_minutos }} min</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('pacientes.show', $ag->paciente_id) }}" class="font-bold text-slate-900 hover:text-teal-600 transition">
                                {{ $ag->paciente->nome }}
                            </a>
                            <span class="text-slate-400 text-[10px] block">{{ $ag->paciente->telefone_formatado ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">{{ $ag->servico->nome ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-800">
                            <span class="inline-block w-2.5 h-2.5 rounded-full mr-1" style="background-color: {{ $ag->profissional->color ?? '#0d9488' }}"></span>
                            {{ $ag->profissional->name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $ag->sala->nome ?? '-' }}</td>
                        <td class="py-3.5 px-4 capitalize">{{ $ag->tipo_cobranca }}</td>
                        <td class="py-3.5 px-4">
                            @php $b = $ag->status_badge; @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $b['bg'] }} {{ $b['text'] }}">
                                {{ $b['label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($ag->status !== 'concluido' && $ag->status !== 'cancelado')
                                    <a href="{{ route('agenda.atendimento', $ag->id) }}"
                                       class="px-2.5 py-1 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">
                                        Atender
                                    </a>
                                @endif
                                <button type="button" @click="agendamentoSelecionado = {{ $ag->id }}; modalRemarcar = true;"
                                        class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="Remarcar">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </button>
                                <button type="button" @click="agendamentoSelecionado = {{ $ag->id }}; modalStatus = true;"
                                        class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="Alterar Situação">
                                    <i data-lucide="check-square" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 font-medium">Nenhum agendamento encontrado no período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <!-- Daily / Weekly Visual Timeline Grid -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($agendamentos as $ag)
                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-teal-300 hover:shadow-md transition bg-gradient-to-br from-white to-slate-50/50 flex flex-col justify-between space-y-3">
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-xl bg-teal-50 text-teal-800 font-bold text-xs">
                                    {{ $ag->data_hora_inicio->format('H:i') }} - {{ $ag->data_hora_fim->format('H:i') }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ $ag->duracao_minutos }} min</span>
                            </div>
                            @php $b = $ag->status_badge; @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $b['bg'] }} {{ $b['text'] }}">
                                {{ $b['label'] }}
                            </span>
                        </div>

                        <!-- Paciente & Serviço -->
                        <div class="mt-3">
                            <a href="{{ route('pacientes.show', $ag->paciente_id) }}" class="font-bold text-slate-900 text-sm hover:text-teal-600 transition block">
                                {{ $ag->paciente->nome }}
                            </a>
                            <span class="text-xs text-slate-500 mt-0.5 block">{{ $ag->servico->nome ?? 'Atendimento Fisioterapêutico' }}</span>
                        </div>

                        <!-- Detalhes Fisioterapeuta e Sala -->
                        <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs text-slate-600">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $ag->profissional->color ?? '#0d9488' }}"></span>
                                <span class="truncate font-medium">{{ $ag->profissional->name }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-slate-500 justify-end truncate">
                                <i data-lucide="door-closed" class="w-3.5 h-3.5"></i>
                                <span class="truncate">{{ $ag->sala->nome ?? 'Sem sala' }}</span>
                            </div>
                        </div>

                        @if($ag->observacoes)
                        <p class="text-[11px] text-slate-400 italic mt-2 line-clamp-2">"{{ $ag->observacoes }}"</p>
                        @endif
                    </div>

                    <!-- Actions Bottom -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="agendamentoSelecionado = {{ $ag->id }}; modalRemarcar = true;"
                                    class="text-xs text-slate-500 hover:text-slate-900 font-semibold px-2 py-1 rounded hover:bg-slate-100 transition">
                                Remarcar
                            </button>
                            <button type="button" @click="agendamentoSelecionado = {{ $ag->id }}; modalStatus = true;"
                                    class="text-xs text-slate-500 hover:text-slate-900 font-semibold px-2 py-1 rounded hover:bg-slate-100 transition">
                                Situação
                            </button>
                        </div>

                        @if($ag->status !== 'concluido' && $ag->status !== 'cancelado')
                        <a href="{{ route('agenda.atendimento', $ag->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition">
                            <i data-lucide="play" class="w-3 h-3"></i> Atender
                        </a>
                        @else
                        <a href="{{ route('prontuarios.show', $ag->paciente_id) }}" class="text-xs text-teal-600 hover:underline font-semibold">
                            Prontuário &rarr;
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full py-16 text-center text-slate-400">
                    <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                    <p class="text-sm font-medium">Nenhum atendimento agendado para o período selecionado.</p>
                    <button type="button" @click="modalNovo = true" class="mt-3 text-xs text-teal-600 font-semibold hover:underline">
                        + Clique para agendar um novo horário
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- MODAL: Novo Agendamento -->
    <div x-show="modalNovo" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200" @click.outside="modalNovo = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-teal-600"></i> Novo Agendamento
                </h3>
                <button type="button" @click="modalNovo = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('agenda.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paciente *</label>
                    <select name="paciente_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                        <option value="">Selecione o paciente...</option>
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}" {{ request()->get('paciente_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nome }} (CPF: {{ $p->cpf_formatado ?? 'Não inf.' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Fisioterapeuta *</label>
                        <select name="profissional_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            @foreach($profissionais as $prof)
                                <option value="{{ $prof->id }}" {{ (string)$profissionalId === (string)$prof->id ? 'selected' : '' }}>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Sala de Atendimento</label>
                            @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('configuracoes.manage'))
                            <a href="{{ route('salas.index') }}" target="_blank" class="text-[10px] text-teal-600 hover:text-teal-700 font-semibold hover:underline flex items-center gap-0.5" title="Abrir configuração de salas em nova aba">
                                <i data-lucide="settings" class="w-3 h-3"></i> Configurar
                            </a>
                            @endif
                        </div>
                        <select name="sala_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <option value="">Qualquer sala / Não definida</option>
                            @foreach($salas as $sala)
                                <option value="{{ $sala->id }}" {{ (string)$salaId === (string)$sala->id ? 'selected' : '' }}>
                                    {{ $sala->nome }} (máx: {{ $sala->capacidade_simultanea }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Serviço *</label>
                    <select name="servico_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                        @foreach($servicos as $srv)
                            <option value="{{ $srv->id }}">
                                {{ $srv->nome }} ({{ $srv->duracao_minutos }} min - R$ {{ number_format($srv->preco_padrao, 2, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Data *</label>
                        <input type="date" name="data" value="{{ $dataRef->format('Y-m-d') }}" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Horário *</label>
                        <input type="time" name="horario" value="09:00" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Duração (min)</label>
                        <input type="number" name="duracao_minutos" value="50" min="15" max="180" step="5" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Forma de Cobrança</label>
                        <select name="tipo_cobranca" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <option value="pacote">Pacote de Sessões (Baixa automática)</option>
                            <option value="avulso">Consulta Avulsa</option>
                            <option value="convenio">Convênio de Saúde</option>
                            <option value="retorno_gratuito">Retorno Gratuito</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Recorrência</label>
                        <select name="recorrencia" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <option value="nenhuma">Apenas este horário (Avulso)</option>
                            <option value="semanal">Semanalmente</option>
                            <option value="quinzenal">A cada 15 dias</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observações Internas</label>
                    <textarea name="observacoes" rows="2" placeholder="Ex: Paciente com dor lombar aguda, necessita maca baixa"
                              class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalNovo = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                        Confirmar Agendamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Alterar Situação / Cancelar -->
    <div x-show="modalStatus" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200" @click.outside="modalStatus = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Atualizar Situação do Atendimento</h3>

            <form :action="'{{ url('agenda/status') }}/' + agendamentoSelecionado" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nova Situação</label>
                    <select name="status" x-model="statusAlvo" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="confirmado">Confirmado (Paciente confirmou presença)</option>
                        <option value="em_espera">Em Espera na Recepção</option>
                        <option value="em_atendimento">Em Atendimento com o Fisioterapeuta</option>
                        <option value="concluido">Concluído (Baixa de sessão e faturamento)</option>
                        <option value="falta">Falta (Paciente não compareceu)</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div x-show="statusAlvo === 'cancelado'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo do Cancelamento *</label>
                    <textarea name="motivo_cancelamento" rows="2" placeholder="Ex: Paciente com compromisso de trabalho imprevisto"
                              class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalStatus = false" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold">
                        Voltar
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-teal-600 text-white text-xs font-semibold">
                        Salvar Alteração
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Remarcar Horário -->
    <div x-show="modalRemarcar" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200" @click.outside="modalRemarcar = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Remarcar Atendimento</h3>

            <form :action="'{{ url('agenda/remarcar') }}/' + agendamentoSelecionado" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nova Data *</label>
                    <input type="date" name="nova_data" required value="{{ now()->format('Y-m-d') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Novo Horário *</label>
                    <input type="time" name="novo_horario" required value="10:00"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alterar Sala (Opcional)</label>
                    <select name="sala_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="">Manter a mesma sala</option>
                        @foreach($salas as $s)
                            <option value="{{ $s->id }}">{{ $s->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalRemarcar = false" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-teal-600 text-white text-xs font-semibold">
                        Remarcar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Bloqueio de Horário -->
    <div x-show="modalBloqueio" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200" @click.outside="modalBloqueio = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-amber-600"></i> Bloquear Horário na Agenda
            </h3>

            <form method="POST" action="{{ route('agenda.bloqueios.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Bloqueio *</label>
                    <input type="text" name="titulo" required placeholder="Ex: Feriado Municipal, Reunião de Equipe, Recesso"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Profissional (Opcional)</label>
                        <select name="profissional_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">Toda a Clínica (Geral)</option>
                            @foreach($profissionais as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sala (Opcional)</label>
                        <select name="sala_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">Todas as Salas</option>
                            @foreach($salas as $s)
                                <option value="{{ $s->id }}">{{ $s->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Data Início *</label>
                        <input type="date" name="data_inicio" value="{{ $dataRef->format('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Início *</label>
                        <input type="time" name="hora_inicio" value="08:00" required class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Data Fim *</label>
                        <input type="date" name="data_fim" value="{{ $dataRef->format('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Fim *</label>
                        <input type="time" name="hora_fim" value="12:00" required class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Bloqueio</label>
                    <select name="tipo" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="intervalo">Intervalo / Almoço</option>
                        <option value="reuniao">Reunião Clínica</option>
                        <option value="feriado">Feriado</option>
                        <option value="recesso">Férias / Recesso</option>
                        <option value="outro">Outro Motivo</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalBloqueio = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 text-white text-xs font-semibold">
                        Registrar Bloqueio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
