@extends('layouts.app')

@section('title', 'Prontuários')
@section('header', 'Prontuários Eletrônicos')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Prontuários Fisioterapêuticos</h1>
            <p class="text-xs text-slate-500">Histórico clínico, avaliações cinético-funcionais e evoluções de sessões.</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('prontuarios.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar por paciente ou CPF..."
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-teal-600 focus:border-teal-600">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm py-2 px-5 rounded-xl transition">
                Buscar
            </button>
            @if($busca)
            <a href="{{ route('prontuarios.index') }}" class="p-2 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-50">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </a>
            @endif
        </form>
    </div>

    <!-- Patients List with Clinical Record Indicators -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                    <th class="py-3.5 px-4">Paciente</th>
                    <th class="py-3.5 px-4">CPF / Contato</th>
                    <th class="py-3.5 px-4">Avaliações</th>
                    <th class="py-3.5 px-4">Evoluções de Sessão</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Prontuário</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pacientes as $paciente)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        <a href="{{ route('prontuarios.show', $paciente->id) }}" class="hover:text-teal-600 text-sm block">
                            {{ $paciente->nome }}
                        </a>
                        <span class="text-slate-400 text-[10px]">{{ $paciente->idade ? "{$paciente->idade} anos" : '' }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        <span>{{ $paciente->cpf_formatado ?? '-' }}</span>
                        <span class="block text-[10px] text-slate-400">{{ $paciente->telefone_formatado ?? '' }}</span>
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-700">
                        {{ $paciente->avaliacoes_count }} avaliação(ões)
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-700">
                        {{ $paciente->evolucoes_count }} sessão(ões)
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $paciente->status === 'ativo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($paciente->status) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <a href="{{ route('prontuarios.show', $paciente->id) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 font-bold transition">
                            <i data-lucide="folder-open" class="w-3.5 h-3.5"></i>
                            <span>Abrir Prontuário</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400 font-medium">Nenhum paciente localizado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($pacientes->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pacientes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
