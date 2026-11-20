@extends('layouts.app')

@section('title', 'Atendimento - ' . $paciente->nome)
@section('header', 'Estação de Atendimento Clínico')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-lg">
                <i data-lucide="stethoscope" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-slate-900">{{ $paciente->nome }}</h1>
                    @php $b = $agendamento->status_badge; @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $b['bg'] }} {{ $b['text'] }}">
                        {{ $b['label'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Horário: <span class="font-bold text-slate-700">{{ $agendamento->data_hora_inicio->format('d/m/Y H:i') }}</span>
                    &bull; {{ $agendamento->servico->nome ?? 'Atendimento Fisioterapêutico' }}
                    &bull; Profissional: <span class="font-bold text-slate-700">{{ $agendamento->profissional->name }}</span>
                    @if($agendamento->sala) &bull; Sala: <span class="text-teal-700 font-semibold">{{ $agendamento->sala->nome }}</span> @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('prontuarios.show', $paciente->id) }}" target="_blank"
               class="px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs font-semibold text-slate-700 flex items-center gap-1.5 transition">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Ver Prontuário Completo</span>
            </a>
            <a href="{{ route('agenda.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-500 hover:bg-slate-50">
                Voltar à Agenda
            </a>
        </div>
    </div>

    <!-- Package & Billing Status Banner -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-xs text-teal-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-teal-600 flex-shrink-0"></i>
            <div>
                @if($agendamento->pacientePacote)
                    <span class="font-bold block">Vinculado ao pacote: {{ $agendamento->pacientePacote->nome_pacote }}</span>
                    <span class="text-teal-700">
                        Saldo atual: {{ $agendamento->pacientePacote->sessoes_restantes }} restante(s) de {{ $agendamento->pacientePacote->sessoes_totais }}.
                        Ao concluir este atendimento, o sistema dará baixa automática em 1 sessão.
                    </span>
                @elseif($agendamento->tipo_cobranca === 'convenio')
                    <span class="font-bold block">Atendimento por Convênio de Saúde</span>
                    <span class="text-teal-700">Verifique a assinatura da guia física ou autorização digital.</span>
                @else
                    <span class="font-bold block">Cobrança Avulsa</span>
                    <span class="text-teal-700">Sessão avulsa particular no valor padrão de R$ {{ number_format($agendamento->servico->preco_padrao ?? 0, 2, ',', '.') }}.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Attendance Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Clinical Evolution Form (2 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-5">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="clipboard-pen" class="w-4 h-4 text-teal-600"></i>
                Registro de Evolução da Sessão (SOAP)
            </h2>

            <form method="POST" action="{{ route('prontuarios.evolucoes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="agendamento_id" value="{{ $agendamento->id }}">
                <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
                <input type="hidden" name="concluir_agendamento" value="1">

                <!-- Escala de Dor EVA Inicial (0 a 10) -->
                <div x-data="{ dorInicial: {{ old('escala_dor_inicial', 5) }} }">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Dor Inicial relatada pelo paciente (EVA: <span class="font-bold text-teal-700" x-text="dorInicial"></span>/10)
                    </label>
                    <div class="flex items-center gap-1.5">
                        <template x-for="n in 11" :key="n-1">
                            <button type="button" @click="dorInicial = n-1"
                                    :class="dorInicial === n-1 ? 'bg-teal-600 text-white font-bold ring-2 ring-teal-400' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="w-8 h-8 rounded-lg text-xs font-semibold transition flex items-center justify-center">
                                <span x-text="n-1"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="escala_dor_inicial" :value="dorInicial">
                </div>

                <!-- Relato Subjetivo -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Subjetivo: Relato do Paciente & Queixa do Dia *
                    </label>
                    <textarea name="relato_subjetivo" rows="2" required placeholder="Como o paciente relatou seus sintomas hoje? Nível de desconforto, queixas diárias..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('relato_subjetivo') }}</textarea>
                </div>

                <!-- Procedimentos Realizados (Conduta) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Objetivo / Conduta: Procedimentos e Recursos Terapêuticos Aplicados *
                    </label>
                    <textarea name="procedimentos_realizados" rows="3" required placeholder="Ex: Liberação miofascial de paravertebrais lombares; Fortalecimento de core com ponte e prancha modificada (3x10); TENS convencional por 20min..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('procedimentos_realizados') }}</textarea>
                </div>

                <!-- Resposta Terapêutica & Dor Final -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Resposta Terapêutica / Avaliação Pós-Sessão</label>
                        <input type="text" name="resposta_terapeutica" value="{{ old('resposta_terapeutica') }}" placeholder="Ex: Paciente relatou alívio imediato e ganho de ADM"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    </div>

                    <div x-data="{ dorFinal: {{ old('escala_dor_final', 2) }} }">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Dor Final Pós-Atendimento (EVA: <span class="font-bold text-teal-700" x-text="dorFinal"></span>/10)
                        </label>
                        <div class="flex items-center gap-1">
                            <template x-for="n in 11" :key="n-1">
                                <button type="button" @click="dorFinal = n-1"
                                        :class="dorFinal === n-1 ? 'bg-emerald-600 text-white font-bold ring-2 ring-emerald-400' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="w-7 h-7 rounded-lg text-xs font-semibold transition flex items-center justify-center">
                                    <span x-text="n-1"></span>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="escala_dor_final" :value="dorFinal">
                    </div>
                </div>

                <!-- Orientações Domiciliares -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Orientações para Casa / Auto-cuidados</label>
                    <input type="text" name="orientacoes_domiciliares" value="{{ old('orientacoes_domiciliares') }}" placeholder="Ex: Alongamento peitoral 2x ao dia; compressa morna por 15 minutos"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>

                <!-- Action Submit -->
                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <span class="text-[11px] text-slate-400">
                        Assinatura: {{ auth()->user()->name }} ({{ auth()->user()->crefito ?? 'Responsável' }})
                    </span>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Salvar Evolução & Concluir Atendimento</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- History & Quick Side info (1 Col) -->
        <div class="space-y-4">
            <!-- Ultimas Evoluções do Paciente -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="history" class="w-4 h-4 text-teal-600"></i> Últimas Evoluções Registradas
                </h3>

                <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @forelse($historicoEvolucoes as $ev)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-xs space-y-1">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                            <span>{{ $ev->data_hora_evolucao->format('d/m/Y') }} (#{{ $ev->numero_sessao }})</span>
                            @if($ev->escala_dor_inicial !== null)
                                <span class="text-rose-600 font-bold">EVA: {{ $ev->escala_dor_inicial }} &rarr; {{ $ev->escala_dor_final }}</span>
                            @endif
                        </div>
                        <p class="text-slate-700 font-medium line-clamp-2">{{ $ev->procedimentos_realizados }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-3 text-center">Primeiro atendimento do paciente.</p>
                    @endforelse
                </div>
            </div>

            <!-- Outras Ações Rápidas -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-2">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Exceções Operacionais</h3>
                <form action="{{ route('agenda.status', $agendamento->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="falta">
                    <button type="submit" onclick="return confirm('Confirmar falta do paciente?');"
                            class="w-full text-left p-2.5 rounded-xl border border-orange-200 bg-orange-50/60 hover:bg-orange-100/60 text-orange-800 text-xs font-semibold transition flex items-center justify-between">
                        <span>Registrar Falta do Paciente</span>
                        <i data-lucide="user-x" class="w-4 h-4"></i>
                    </button>
                </form>

                <form action="{{ route('agenda.status', $agendamento->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="concluido">
                    <button type="submit" onclick="return confirm('Concluir atendimento sem preencher nova evolução clínica agora?');"
                            class="w-full text-left p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition flex items-center justify-between">
                        <span>Concluir sem Evolução</span>
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
