@extends('layouts.app')

@section('title', 'Configurações da Clínica')
@section('header', 'Configurações da Clínica')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Configurações Gerais</h1>
            <p class="text-xs text-slate-500">Dados institucionais da clínica, horários de funcionamento e políticas de atendimento.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('salas.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold hover:bg-slate-50">
                Salas
            </a>
            <a href="{{ route('servicos.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold hover:bg-slate-50">
                Serviços
            </a>
            <a href="{{ route('pacotes.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold hover:bg-slate-50">
                Pacotes
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('configuracoes.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Dados Institucionais -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="building" class="w-4 h-4 text-teal-600"></i>
                Identificação & Dados Fiscais
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Fantasia *</label>
                    <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $config->nome_fantasia) }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Razão Social *</label>
                    <input type="text" name="razao_social" value="{{ old('razao_social', $config->razao_social) }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">CNPJ</label>
                    <input type="text" name="cnpj" value="{{ old('cnpj', $config->cnpj) }}" placeholder="00.000.000/0001-00"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Registro CREFITO PJ</label>
                    <input type="text" name="crefito_pj" value="{{ old('crefito_pj', $config->crefito_pj) }}" placeholder="12345-SP/PJ"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Responsável Técnico (RT)</label>
                    <input type="text" name="responsavel_tecnico" value="{{ old('responsavel_tecnico', $config->responsavel_tecnico) }}" placeholder="Dra. Fabris - CREFITO-3/..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
            </div>
        </div>

        <!-- 2. Contatos & Endereço -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-teal-600"></i>
                Endereço & Contatos para Documentos
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone Fixo</label>
                    <input type="text" name="telefone" value="{{ old('telefone', $config->telefone) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $config->whatsapp) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail Institucional</label>
                    <input type="email" name="email" value="{{ old('email', $config->email) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">CEP</label>
                    <input type="text" name="cep" value="{{ old('cep', $config->cep) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-7">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Logradouro</label>
                    <input type="text" name="logradouro" value="{{ old('logradouro', $config->logradouro) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Número</label>
                    <input type="text" name="numero" value="{{ old('numero', $config->numero) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bairro</label>
                    <input type="text" name="bairro" value="{{ old('bairro', $config->bairro) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade', $config->cidade) }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">UF</label>
                    <input type="text" name="estado" value="{{ old('estado', $config->estado) }}" maxlength="2"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 uppercase">
                </div>
            </div>
        </div>

        <!-- 3. Horários & Políticas Operacionais -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="clock" class="w-4 h-4 text-teal-600"></i>
                Horários de Funcionamento & Políticas de Agenda
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Horário de Abertura *</label>
                    <input type="time" name="horario_abertura" value="{{ old('horario_abertura', $config->horario_abertura) }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Horário de Fechamento *</label>
                    <input type="time" name="horario_fechamento" value="{{ old('horario_fechamento', $config->horario_fechamento) }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Intervalo Padrão da Agenda (min)</label>
                    <input type="number" name="intervalo_agenda_minutos" value="{{ old('intervalo_agenda_minutos', $config->intervalo_agenda_minutos) }}" min="10" max="120" step="5" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Antecedência Cancelamento (horas)</label>
                    <input type="number" name="politica_cancelamento_horas" value="{{ old('politica_cancelamento_horas', $config->politica_cancelamento_horas) }}" min="0" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-12 pt-2">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                        <input type="checkbox" id="politica_falta_cobra_sessao" name="politica_falta_cobra_sessao" value="1"
                               {{ old('politica_falta_cobra_sessao', $config->politica_falta_cobra_sessao) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        <label for="politica_falta_cobra_sessao" class="text-xs text-slate-700 font-semibold cursor-pointer">
                            Política de Falta: Dar baixa na sessão do pacote quando o paciente faltar sem aviso prévio.
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition">
                Salvar Configurações
            </button>
        </div>
    </form>
</div>
@endsection
