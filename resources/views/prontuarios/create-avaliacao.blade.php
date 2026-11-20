@extends('layouts.app')

@section('title', 'Nova Avaliação - ' . $paciente->nome)
@section('header', 'Avaliação Fisioterapêutica Inicial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Avaliação Clínica: {{ $paciente->nome }}</h1>
            <p class="text-xs text-slate-500">Exame físico, funcional e planejamento terapêutico.</p>
        </div>
        <a href="{{ route('prontuarios.show', $paciente->id) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao prontuário
        </a>
    </div>

    <form method="POST" action="{{ route('prontuarios.avaliacoes.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">

        <!-- 1. Dados Básicos da Avaliação -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="info" class="w-4 h-4 text-teal-600"></i> Identificação da Avaliação
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-8">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Título da Avaliação *</label>
                    <input type="text" name="titulo" required
                           value="{{ old('titulo', 'Avaliação Fisioterapêutica Traumato-Ortopédica') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data da Avaliação *</label>
                    <input type="date" name="data_avaliacao" required
                           value="{{ old('data_avaliacao', now()->format('Y-m-d')) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
            </div>
        </div>

        <!-- 2. Anamnese & Queixa -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="message-square" class="w-4 h-4 text-teal-600"></i> Anamnese & Histórico Clínico
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Queixa Principal (QP) *</label>
                    <textarea name="queixa_principal" rows="2" required placeholder="Motivo que levou o paciente a buscar assistência fisioterapêutica..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('queixa_principal') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">História da Doença Atual (HDA)</label>
                    <textarea name="historia_doenca_atual" rows="2" placeholder="Início dos sintomas, evolução temporal, mecanismo de trauma ou lesão..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('historia_doenca_atual') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Histórico Pregresso & Cirurgias (HPP)</label>
                        <textarea name="historia_patologica_pregressa" rows="2" placeholder="Doenças crônicas, cirurgias, fraturas prévias..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('historia_patologica_pregressa') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Medicações em Uso</label>
                        <textarea name="medicacoes_em_uso" rows="2" placeholder="Analgésicos, anti-inflamatórios, relaxantes musculares..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('medicacoes_em_uso') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Avaliação da Dor -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4" x-data="{ dorEva: {{ old('escala_dor_eva', 5) }} }">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="flame" class="w-4 h-4 text-rose-600"></i> Avaliação da Dor (Escala Visual Analógica - EVA)
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Intensidade da Dor (0 a 10): <span class="font-bold text-teal-700 text-sm" x-text="dorEva"></span>
                </label>
                <div class="flex items-center gap-2">
                    <template x-for="n in 11" :key="n-1">
                        <button type="button" @click="dorEva = n-1"
                                :class="dorEva === n-1 ? 'bg-teal-600 text-white font-bold ring-2 ring-teal-400' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="w-9 h-9 rounded-xl text-xs font-semibold transition flex items-center justify-center">
                            <span x-text="n-1"></span>
                        </button>
                    </template>
                </div>
                <input type="hidden" name="escala_dor_eva" :value="dorEva">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Localização Anatômica da Dor</label>
                    <input type="text" name="localizacao_dor" value="{{ old('localizacao_dor') }}" placeholder="Ex: Região lombar L4-S1 com irradiação ciática"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo / Característica da Dor</label>
                    <input type="text" name="caracteristica_dor" value="{{ old('caracteristica_dor') }}" placeholder="Ex: Queimação, pontada, peso, latejante"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fatores de Melhora</label>
                    <input type="text" name="fatores_melhora" value="{{ old('fatores_melhora') }}" placeholder="Ex: Repouso, calor local"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fatores de Piora</label>
                    <input type="text" name="fatores_piora" value="{{ old('fatores_piora') }}" placeholder="Ex: Permanecer sentado por mais de 30min"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
            </div>
        </div>

        <!-- 4. Exame Físico e Funcional -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="activity" class="w-4 h-4 text-teal-600"></i> Exame Físico & Testes Funcionais
            </h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inspeção Estática, Dinâmica & Palpação</label>
                        <textarea name="inspecao_palpacao" rows="2" placeholder="Pontos gatilho, tônus muscular, edema, cicatrizes..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('inspecao_palpacao') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Amplitude de Movimento (ADM / Goniometria)</label>
                        <textarea name="amplitude_movimento" rows="2" placeholder="Graus de flexão, extensão, rotações, restrições articulares..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('amplitude_movimento') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Força Muscular (Escala de Oxford)</label>
                        <textarea name="forca_muscular" rows="2" placeholder="Graus de força por grupo muscular avaliado..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('forca_muscular') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Testes Especiais Ortopédicos / Neurológicos</label>
                        <textarea name="testes_especiais_ortopedicos" rows="2" placeholder="Ex: Lasègue (+), Neer (-), Gaveta anterior (-)..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('testes_especiais_ortopedicos') }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Avaliação Postural Global</label>
                    <input type="text" name="avaliacao_postural" value="{{ old('avaliacao_postural') }}" placeholder="Ex: Hiperlordose lombar, anteriorização de cabeça, escoliose em C..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>
            </div>
        </div>

        <!-- 5. Diagnóstico e Plano Terapêutico -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="target" class="w-4 h-4 text-teal-600"></i> Diagnóstico Cinético-Funcional & Plano
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Diagnóstico Fisioterapêutico Cinético-Funcional *</label>
                    <textarea name="diagnostico_fisioterapeutico" rows="2" required placeholder="Descrição da disfunção motora, biomecânica e funcional..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('diagnostico_fisioterapeutico') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Objetivos Terapêuticos (Curto, Médio e Longo Prazo) *</label>
                    <textarea name="objetivos_terapeuticos" rows="2" required placeholder="Alívio da dor, restauração da ADM, fortalecimento, retorno às atividades..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('objetivos_terapeuticos') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Plano de Tratamento & Condutas Fisioterapêuticas *</label>
                        <textarea name="plano_tratamento" rows="2" required placeholder="Cinesioterapia motora, terapia manual, eletroterapia, RPG, Pilates..."
                                  class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">{{ old('plano_tratamento') }}</textarea>
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Frequência Semanal Sugerida</label>
                        <input type="text" name="frequencia_semanal_sugerida" value="{{ old('frequencia_semanal_sugerida', '2 vezes por semana') }}"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit & Legal Notice -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-500 space-y-1">
            <span class="font-bold text-slate-700 block">Assinatura & Conformidade Legal:</span>
            <p>
                Ao salvar, este registro clínico será finalizado e assinado digitalmente por <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->crefito ?? 'Fisioterapeuta' }}). Conforme determina o COFFITO e a LGPD, os registros finalizados são imutáveis e quaisquer complementações deverão ser registradas como aditivos justificados.
            </p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('prontuarios.show', $paciente->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition">
                Salvar & Assinar Avaliação
            </button>
        </div>
    </form>
</div>
@endsection
