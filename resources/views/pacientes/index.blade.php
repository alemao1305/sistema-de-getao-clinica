@extends('layouts.app')

@section('title', 'Pacientes')
@section('header', 'Gestão de Pacientes')

@section('content')
<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Cadastro de Pacientes</h1>
            <p class="text-xs text-slate-500">Localize, cadastre e gerencie o histórico dos pacientes da clínica.</p>
        </div>
        <a href="{{ route('pacientes.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Novo Paciente</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('pacientes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <label for="busca" class="block text-xs font-semibold text-slate-600 mb-1">Buscar por Nome, CPF ou Telefone</label>
                <div class="relative">
                    <input type="text" id="busca" name="busca" value="{{ $busca }}" placeholder="Ex: Ana Beatriz ou 123.456.789-01"
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="status" class="block text-xs font-semibold text-slate-600 mb-1">Situação</label>
                <select id="status" name="status"
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-1 focus:ring-teal-600 focus:border-teal-600">
                    <option value="">Todas as situações</option>
                    <option value="ativo" {{ $status === 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ $status === 'inativo' ? 'selected' : '' }}>Inativo</option>
                    <option value="alta" {{ $status === 'alta' ? 'selected' : '' }}>Alta Fisioterapêutica</option>
                    <option value="transferido" {{ $status === 'transferido' ? 'selected' : '' }}>Transferido</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm py-2 px-4 rounded-xl transition">
                    Filtrar
                </button>
                @if($busca || $status || $convenio)
                <a href="{{ route('pacientes.index') }}"
                   class="p-2 text-slate-500 hover:text-slate-800 border border-slate-200 rounded-xl hover:bg-slate-50 transition" title="Limpar filtros">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Patients Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Paciente</th>
                        <th class="py-3.5 px-4">Contatos</th>
                        <th class="py-3.5 px-4">Cidade/UF</th>
                        <th class="py-3.5 px-4">Convênio / Origem</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pacientes as $paciente)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('pacientes.show', $paciente->id) }}" class="font-bold text-slate-900 hover:text-teal-600 transition block">
                                {{ $paciente->nome }}
                            </a>
                            <span class="text-xs text-slate-500">
                                CPF: {{ $paciente->cpf_formatado ?? 'Não informado' }}
                                @if($paciente->idade)
                                    &bull; {{ $paciente->idade }} anos
                                @endif
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="block text-slate-800">{{ $paciente->telefone_formatado ?? '-' }}</span>
                            <span class="text-xs text-slate-500">{{ $paciente->email ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $paciente->cidade ? "{$paciente->cidade}/{$paciente->estado}" : '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($paciente->possui_convenio)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    <i data-lucide="shield" class="w-3 h-3"></i>
                                    {{ $paciente->convenio_nome ?? 'Convênio' }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500 font-medium">Particular</span>
                            @endif
                            @if($paciente->origem_paciente)
                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $paciente->origem_paciente }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $statusBadges = [
                                    'ativo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'inativo' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    'alta' => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'transferido' => 'bg-purple-50 text-purple-700 border-purple-200',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusBadges[$paciente->status] ?? 'bg-slate-100' }}">
                                {{ ucfirst($paciente->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('pacientes.show', $paciente->id) }}"
                                   title="Prontuário e Ficha"
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-slate-100 transition">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('pacientes.edit', $paciente->id) }}"
                                   title="Editar Dados"
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i data-lucide="user-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                            <p class="font-medium">Nenhum paciente localizado com os filtros selecionados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pacientes->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pacientes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
