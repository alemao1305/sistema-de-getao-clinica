@extends('layouts.app')

@section('title', 'Editar Paciente')
@section('header', 'Editar Dados do Paciente')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Editar Paciente: {{ $paciente->nome }}</h1>
            <p class="text-xs text-slate-500">Atualize as informações cadastrais e administrativas.</p>
        </div>
        <a href="{{ route('pacientes.show', $paciente->id) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao perfil
        </a>
    </div>

    <form method="POST" action="{{ route('pacientes.update', $paciente->id) }}" class="space-y-6" x-data="{ temConvenio: {{ old('possui_convenio', $paciente->possui_convenio) ? 'true' : 'false' }} }">
        @csrf
        @method('PUT')

        <!-- Status & Dados Básicos -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-teal-600"></i>
                    Identificação & Situação
                </h2>
                <div class="flex items-center gap-2">
                    <label for="status" class="text-xs font-semibold text-slate-700">Situação:</label>
                    <select id="status" name="status" class="text-xs font-semibold rounded-lg border border-slate-300 p-1.5 focus:ring-teal-600">
                        <option value="ativo" {{ old('status', $paciente->status) === 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ old('status', $paciente->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                        <option value="alta" {{ old('status', $paciente->status) === 'alta' ? 'selected' : '' }}>Alta Fisioterapêutica</option>
                        <option value="transferido" {{ old('status', $paciente->status) === 'transferido' ? 'selected' : '' }}>Transferido</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-8">
                    <label for="nome" class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" value="{{ old('nome', $paciente->nome) }}" required
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="cpf" class="block text-xs font-semibold text-slate-700 mb-1">CPF</label>
                    <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $paciente->cpf) }}" placeholder="000.000.000-00"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="rg" class="block text-xs font-semibold text-slate-700 mb-1">RG</label>
                    <input type="text" id="rg" name="rg" value="{{ old('rg', $paciente->rg) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="data_nascimento" class="block text-xs font-semibold text-slate-700 mb-1">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento', $paciente->data_nascimento?->format('Y-m-d')) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="sexo" class="block text-xs font-semibold text-slate-700 mb-1">Sexo Biológico</label>
                    <select id="sexo" name="sexo"
                            class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                        <option value="">Selecione...</option>
                        <option value="F" {{ old('sexo', $paciente->sexo) === 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="M" {{ old('sexo', $paciente->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="Outro" {{ old('sexo', $paciente->sexo) === 'Outro' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>

                <div class="sm:col-span-6">
                    <label for="profissao" class="block text-xs font-semibold text-slate-700 mb-1">Profissão / Ocupação</label>
                    <input type="text" id="profissao" name="profissao" value="{{ old('profissao', $paciente->profissao) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-6">
                    <label for="estado_civil" class="block text-xs font-semibold text-slate-700 mb-1">Estado Civil</label>
                    <select id="estado_civil" name="estado_civil"
                            class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                        <option value="">Selecione...</option>
                        <option value="Solteiro(a)" {{ old('estado_civil', $paciente->estado_civil) === 'Solteiro(a)' ? 'selected' : '' }}>Solteiro(a)</option>
                        <option value="Casado(a)" {{ old('estado_civil', $paciente->estado_civil) === 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                        <option value="Divorciado(a)" {{ old('estado_civil', $paciente->estado_civil) === 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                        <option value="Viúvo(a)" {{ old('estado_civil', $paciente->estado_civil) === 'Viúvo(a)' ? 'selected' : '' }}>Viúvo(a)</option>
                        <option value="União Estável" {{ old('estado_civil', $paciente->estado_civil) === 'União Estável' ? 'selected' : '' }}>União Estável</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contatos & Emergência -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="phone" class="w-4 h-4 text-teal-600"></i>
                Contatos & Emergência
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-4">
                    <label for="whatsapp" class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Principal</label>
                    <input type="text" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $paciente->whatsapp) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="telefone" class="block text-xs font-semibold text-slate-700 mb-1">Telefone Fixo / Recado</label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $paciente->telefone) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $paciente->email) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-6">
                    <label for="contato_emergencia_nome" class="block text-xs font-semibold text-slate-700 mb-1">Nome do Contato de Emergência</label>
                    <input type="text" id="contato_emergencia_nome" name="contato_emergencia_nome" value="{{ old('contato_emergencia_nome', $paciente->contato_emergencia_nome) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-6">
                    <label for="contato_emergencia_telefone" class="block text-xs font-semibold text-slate-700 mb-1">Telefone de Emergência</label>
                    <input type="text" id="contato_emergencia_telefone" name="contato_emergencia_telefone" value="{{ old('contato_emergencia_telefone', $paciente->contato_emergencia_telefone) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>
            </div>
        </div>

        <!-- Endereço -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-teal-600"></i>
                Endereço
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3">
                    <label for="cep" class="block text-xs font-semibold text-slate-700 mb-1">CEP</label>
                    <input type="text" id="cep" name="cep" value="{{ old('cep', $paciente->cep) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-7">
                    <label for="logradouro" class="block text-xs font-semibold text-slate-700 mb-1">Logradouro</label>
                    <input type="text" id="logradouro" name="logradouro" value="{{ old('logradouro', $paciente->logradouro) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-2">
                    <label for="numero" class="block text-xs font-semibold text-slate-700 mb-1">Número</label>
                    <input type="text" id="numero" name="numero" value="{{ old('numero', $paciente->numero) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="complemento" class="block text-xs font-semibold text-slate-700 mb-1">Complemento</label>
                    <input type="text" id="complemento" name="complemento" value="{{ old('complemento', $paciente->complemento) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label for="bairro" class="block text-xs font-semibold text-slate-700 mb-1">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro', $paciente->bairro) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-3">
                    <label for="cidade" class="block text-xs font-semibold text-slate-700 mb-1">Cidade</label>
                    <input type="text" id="cidade" name="cidade" value="{{ old('cidade', $paciente->cidade) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-1">
                    <label for="estado" class="block text-xs font-semibold text-slate-700 mb-1">UF</label>
                    <input type="text" id="estado" name="estado" value="{{ old('estado', $paciente->estado) }}" maxlength="2"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 uppercase focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>
            </div>
        </div>

        <!-- Convênio -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-teal-600"></i>
                    Convênio / Plano de Saúde
                </h2>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="possui_convenio" value="1" x-model="temConvenio"
                           class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    Paciente utiliza convênio
                </label>
            </div>

            <div x-show="temConvenio" x-transition class="grid grid-cols-1 sm:grid-cols-12 gap-4 pt-2">
                <div class="sm:col-span-4">
                    <label for="convenio_nome" class="block text-xs font-semibold text-slate-700 mb-1">Nome do Convênio</label>
                    <input type="text" id="convenio_nome" name="convenio_nome" value="{{ old('convenio_nome', $paciente->convenio_nome) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-3">
                    <label for="convenio_plano" class="block text-xs font-semibold text-slate-700 mb-1">Plano / Categoria</label>
                    <input type="text" id="convenio_plano" name="convenio_plano" value="{{ old('convenio_plano', $paciente->convenio_plano) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-3">
                    <label for="convenio_carteirinha" class="block text-xs font-semibold text-slate-700 mb-1">Nº da Carteirinha</label>
                    <input type="text" id="convenio_carteirinha" name="convenio_carteirinha" value="{{ old('convenio_carteirinha', $paciente->convenio_carteirinha) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>

                <div class="sm:col-span-2">
                    <label for="convenio_validade" class="block text-xs font-semibold text-slate-700 mb-1">Validade Carteira</label>
                    <input type="date" id="convenio_validade" name="convenio_validade" value="{{ old('convenio_validade', $paciente->convenio_validade?->format('Y-m-d')) }}"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-between pt-2">
            @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('pacientes.delete'))
            <button type="button" onclick="if(confirm('Tem certeza que deseja arquivar este paciente?')) { document.getElementById('delete-form').submit(); }"
                    class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                Arquivar Paciente
            </button>
            @else
            <div></div>
            @endif

            <div class="flex items-center gap-3">
                <a href="{{ route('pacientes.show', $paciente->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold shadow-sm transition">
                    Atualizar Dados
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('pacientes.destroy', $paciente->id) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
