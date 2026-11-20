@extends('layouts.app')

@section('title', 'Prontuário - ' . $paciente->nome)
@section('header', 'Prontuário Fisioterapêutico Eletrônico')

@section('content')
<div class="space-y-6" x-data="{
    modalEvolucao: false,
    modalAditivo: false,
    modalDeclaracao: false,
    registroTipo: 'evolucao',
    registroId: null,
    registroTitulo: ''
}">
    <!-- Top Patient Bar -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-bold text-lg">
                <i data-lucide="folder-heart" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900">{{ $paciente->nome }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-800">
                        Prontuário Nº #{{ str_pad($paciente->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                    <span>CPF: {{ $paciente->cpf_formatado ?? '-' }}</span>
                    <span>&bull;</span>
                    <span>{{ $paciente->idade ? "{$paciente->idade} anos" : 'Idade não inf.' }}</span>
                    <span>&bull;</span>
                    <span>Contato: {{ $paciente->telefone_formatado ?? '-' }}</span>
                    @if($paciente->possui_convenio)
                        <span>&bull;</span>
                        <span class="font-bold text-blue-700">Convênio: {{ $paciente->convenio_nome }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('prontuarios.avaliacoes.create', $paciente->id) }}"
               class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="clipboard-plus" class="w-4 h-4"></i>
                <span>Nova Avaliação</span>
            </a>
            <button type="button" @click="modalEvolucao = true"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Registrar Evolução</span>
            </button>
            <button type="button" @click="modalDeclaracao = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Emitir Declaração</span>
            </button>
            <a href="{{ route('pacientes.show', $paciente->id) }}"
               class="p-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition" title="Ver Cadastro do Paciente">
                <i data-lucide="user" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- 1. Avaliações Clínicas Realizadas -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-teal-600"></i>
                Avaliações Cinético-Funcionais ({{ $paciente->avaliacoes->count() }})
            </h2>
            <a href="{{ route('prontuarios.avaliacoes.create', $paciente->id) }}" class="text-xs font-semibold text-teal-600 hover:underline">
                + Nova Avaliação
            </a>
        </div>

        @forelse($paciente->avaliacoes as $av)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $av->titulo }}</h3>
                    <p class="text-xs text-slate-500">
                        Data: {{ $av->data_avaliacao->format('d/m/Y') }} &bull;
                        Fisioterapeuta: <span class="font-semibold text-slate-700">{{ $av->profissional->name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800">
                        Dor Inicial EVA: {{ $av->escala_dor_eva }}/10
                    </span>
                    <button type="button" @click="registroTipo = 'avaliacao'; registroId = {{ $av->id }}; registroTitulo = '{{ addslashes($av->titulo) }}'; modalAditivo = true;"
                            class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-[11px] font-semibold text-slate-600 flex items-center gap-1">
                        <i data-lucide="edit-3" class="w-3 h-3"></i> Aditivo / Retificação
                    </button>
                </div>
            </div>

            <!-- Grid de Campos da Avaliação -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1">
                    <span class="font-bold text-slate-700 block">Queixa Principal:</span>
                    <p class="text-slate-600 leading-relaxed">{{ $av->queixa_principal }}</p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1">
                    <span class="font-bold text-slate-700 block">Histórico da Doença Atual (HDA):</span>
                    <p class="text-slate-600 leading-relaxed">{{ $av->historia_doenca_atual ?: 'Não relatado' }}</p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1">
                    <span class="font-bold text-slate-700 block">Características da Dor & Localização:</span>
                    <p class="text-slate-600">Localização: {{ $av->localizacao_dor ?: 'Não informada' }}</p>
                    <p class="text-slate-600">Tipo: {{ $av->caracteristica_dor ?: 'Geral' }}</p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1">
                    <span class="font-bold text-slate-700 block">Histórico Pregresso / Comorbidades:</span>
                    <p class="text-slate-600">{{ $av->historia_patologica_pregressa ?: 'Nega cirurgias ou traumas prévios' }}</p>
                </div>

                @if($av->inspecao_palpacao || $av->amplitude_movimento || $av->testes_especiais_ortopedicos)
                <div class="col-span-full bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-2">
                    <span class="font-bold text-slate-700 block">Exame Físico & Testes Funcionais:</span>
                    @if($av->inspecao_palpacao)<p class="text-slate-600"><span class="font-semibold">Inspeção/Palpação:</span> {{ $av->inspecao_palpacao }}</p>@endif
                    @if($av->amplitude_movimento)<p class="text-slate-600"><span class="font-semibold">Goniometria / ADM:</span> {{ $av->amplitude_movimento }}</p>@endif
                    @if($av->testes_especiais_ortopedicos)<p class="text-slate-600"><span class="font-semibold">Testes Especiais:</span> {{ $av->testes_especiais_ortopedicos }}</p>@endif
                </div>
                @endif

                <div class="bg-teal-50/70 p-3.5 rounded-xl border border-teal-100 space-y-1">
                    <span class="font-bold text-teal-900 block">Diagnóstico Cinético-Funcional:</span>
                    <p class="text-teal-950 font-medium">{{ $av->diagnostico_fisioterapeutico }}</p>
                </div>

                <div class="bg-teal-50/70 p-3.5 rounded-xl border border-teal-100 space-y-1">
                    <span class="font-bold text-teal-900 block">Objetivos Terapêuticos & Plano:</span>
                    <p class="text-teal-950 font-medium">{{ $av->objetivos_terapeuticos }}</p>
                    <p class="text-teal-800 text-[11px] mt-1">Conduta: {{ $av->plano_tratamento }}</p>
                </div>
            </div>

            <!-- Aditivos / Retificações Anexadas -->
            @if($av->aditivos->isNotEmpty())
            <div class="pt-3 border-t border-slate-100 space-y-2">
                <span class="text-[11px] font-bold text-amber-700 uppercase flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Retificações / Aditivos Éticos Registrados
                </span>
                @foreach($av->aditivos as $aditivo)
                <div class="bg-amber-50/60 p-3 rounded-xl border border-amber-200 text-xs text-amber-950 space-y-1">
                    <div class="flex items-center justify-between text-[11px] text-amber-800 font-semibold">
                        <span>Aditivo registrado em {{ $aditivo->created_at->format('d/m/Y H:i') }} por {{ $aditivo->autor->name }}</span>
                        <span class="italic">Justificativa: {{ $aditivo->justificativa }}</span>
                    </div>
                    <p class="font-medium text-slate-800">{{ $aditivo->conteudo_aditivo }}</p>
                </div>
                @endforeach
            </div>
            @endif

            <div class="pt-2 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Assinatura Digital: {{ $av->assinatura_profissional }}</span>
                <span>Finalizado em {{ $av->finalizado_em ? $av->finalizado_em->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
        @empty
        <div class="bg-white p-6 rounded-2xl text-center text-slate-400 border border-slate-200">
            <i data-lucide="file-question" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
            <p class="text-xs font-semibold">Nenhuma avaliação clínica registrada ainda.</p>
            <a href="{{ route('prontuarios.avaliacoes.create', $paciente->id) }}" class="mt-2 inline-block text-xs text-teal-600 font-bold hover:underline">
                + Preencher Avaliação Fisioterapêutica Inicial
            </a>
        </div>
        @endforelse
    </div>

    <!-- 2. Evoluções de Sessão Cronológicas -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-teal-600"></i>
                Evoluções de Sessões Clínicas ({{ $paciente->evolucoes->count() }})
            </h2>
            <button type="button" @click="modalEvolucao = true" class="text-xs font-semibold text-emerald-600 hover:underline">
                + Nova Evolução
            </button>
        </div>

        <div class="space-y-4">
            @forelse($paciente->evolucoes as $ev)
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 font-bold text-xs">
                            Sessão #{{ $ev->numero_sessao }}
                        </span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $ev->data_hora_evolucao->format('d/m/Y H:i') }}
                        </span>
                        <span class="text-xs text-slate-400">&bull; {{ $ev->profissional->name }} ({{ $ev->profissional->crefito ?? 'Fisioterapeuta' }})</span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($ev->escala_dor_inicial !== null)
                        <div class="text-xs font-semibold">
                            <span class="text-slate-500">Dor EVA:</span>
                            <span class="font-bold text-rose-600">{{ $ev->escala_dor_inicial }}/10</span>
                            @if($ev->escala_dor_final !== null)
                                <span class="text-slate-400">&rarr;</span>
                                <span class="font-bold text-emerald-600">{{ $ev->escala_dor_final }}/10</span>
                            @endif
                        </div>
                        @endif

                        <button type="button" @click="registroTipo = 'evolucao'; registroId = {{ $ev->id }}; registroTitulo = 'Sessão #{{ $ev->numero_sessao }} ({{ $ev->data_hora_evolucao->format('d/m/Y') }})'; modalAditivo = true;"
                                class="p-1 rounded text-slate-400 hover:text-slate-700" title="Inserir Retificação/Aditivo">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <div class="text-xs space-y-2">
                    <div>
                        <span class="font-bold text-slate-700">Relato Subjetivo:</span>
                        <p class="text-slate-600 mt-0.5">{{ $ev->relato_subjetivo }}</p>
                    </div>
                    <div>
                        <span class="font-bold text-slate-700">Procedimentos / Conduta Aplicada:</span>
                        <p class="text-slate-600 mt-0.5">{{ $ev->procedimentos_realizados }}</p>
                    </div>
                    @if($ev->resposta_terapeutica)
                    <div>
                        <span class="font-bold text-slate-700">Resposta Pós-Atendimento:</span>
                        <p class="text-slate-600 mt-0.5">{{ $ev->resposta_terapeutica }}</p>
                    </div>
                    @endif
                    @if($ev->orientacoes_domiciliares)
                    <div>
                        <span class="font-bold text-slate-700">Orientações Domiciliares:</span>
                        <p class="text-slate-600 mt-0.5">{{ $ev->orientacoes_domiciliares }}</p>
                    </div>
                    @endif
                </div>

                <!-- Aditivos da Evolução -->
                @if($ev->aditivos->isNotEmpty())
                <div class="pt-2 border-t border-slate-100 space-y-1.5">
                    @foreach($ev->aditivos as $ad)
                    <div class="bg-amber-50/60 p-2.5 rounded-xl border border-amber-200 text-[11px] text-amber-950">
                        <div class="font-semibold text-amber-800">
                            Retificação em {{ $ad->created_at->format('d/m/Y H:i') }} ({{ $ad->justificativa }}):
                        </div>
                        <p class="mt-0.5 text-slate-800">{{ $ad->conteudo_aditivo }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="pt-2 text-[10px] text-slate-400 flex items-center justify-between border-t border-slate-50">
                    <span>Assinado por: {{ $ev->assinatura_profissional ?? $ev->profissional->name }}</span>
                    <span>Registro finalizado e auditado</span>
                </div>
            </div>
            @empty
            <div class="bg-white p-6 rounded-2xl text-center text-slate-400 border border-slate-200">
                <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                <p class="text-xs font-semibold">Nenhuma evolução de sessão registrada.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL: Nova Evolução de Sessão -->
    <div x-show="modalEvolucao" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200" @click.outside="modalEvolucao = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="clipboard-pen" class="w-5 h-5 text-teal-600"></i> Registrar Nova Evolução
                </h3>
                <button type="button" @click="modalEvolucao = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('prontuarios.evolucoes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Data/Hora da Sessão</label>
                        <input type="datetime-local" name="data_hora_evolucao" value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dor Inicial (EVA 0-10)</label>
                        <input type="number" name="escala_dor_inicial" min="0" max="10" value="5"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Relato Subjetivo do Paciente *</label>
                    <textarea name="relato_subjetivo" rows="2" required placeholder="Como o paciente relatou seus sintomas hoje..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Procedimentos Realizados (Conduta) *</label>
                    <textarea name="procedimentos_realizados" rows="3" required placeholder="Exercícios, aparelhos, técnicas manuais..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Resposta Terapêutica</label>
                        <input type="text" name="resposta_terapeutica" placeholder="Ex: Alívio de dor, ganho de ADM"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dor Final (EVA 0-10)</label>
                        <input type="number" name="escala_dor_final" min="0" max="10" value="2"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Orientações Domiciliares</label>
                    <input type="text" name="orientacoes_domiciliares" placeholder="Exercícios domiciliares, compressa, postura..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalEvolucao = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm">
                        Salvar e Assinar Evolução
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Aditivo / Retificação Não-Destrutiva -->
    <div x-show="modalAditivo" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200" @click.outside="modalAditivo = false">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Aditivo Retificador de Prontuário</h3>
            <p class="text-xs text-slate-500 mb-4">
                O registro anterior não será apagado. Conforme normas legais, a retificação fica anexada com data e autor.
            </p>

            <form method="POST" action="{{ route('prontuarios.aditivos.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="registro_tipo" :value="registroTipo">
                <input type="hidden" name="registro_id" :value="registroId">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Justificativa da Retificação *</label>
                    <input type="text" name="justificativa" required placeholder="Ex: Correção de digitação na conduta / Esclarecimento complementar"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Texto Aditivo / Retificação *</label>
                    <textarea name="conteudo_aditivo" rows="4" required placeholder="Descreva os dados retificados ou complementares..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalAditivo = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm">
                        Registrar Aditivo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Emitir Declaração / Atestado -->
    <div x-show="modalDeclaracao" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200" @click.outside="modalDeclaracao = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-teal-600"></i> Emitir Documento / Declaração
            </h3>

            <form method="GET" action="{{ route('prontuarios.declaracao', $paciente->id) }}" target="_blank" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Declaração</label>
                    <select name="tipo" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="comparecimento">Declaração de Comparecimento</option>
                        <option value="tratamento">Atestado / Declaração de Tratamento Fisioterapêutico</option>
                        <option value="alta">Declaração de Alta Fisioterapêutica</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Período / Horário de Comparecimento</label>
                    <input type="text" name="periodo" value="no dia de hoje das {{ now()->format('H:i') }} às {{ now()->addMinutes(50)->format('H:i') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observações Complementares</label>
                    <textarea name="observacoes" rows="3" class="w-full text-xs rounded-xl border border-slate-300 p-2">Declaro que o(a) paciente acima identificado(a) esteve sob assistência fisioterapêutica nas dependências desta clínica para tratamento e reabilitação cinético-funcional.</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalDeclaracao = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Fechar
                    </button>
                    <button type="submit" @click="modalDeclaracao = false" class="px-5 py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5">
                        <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
