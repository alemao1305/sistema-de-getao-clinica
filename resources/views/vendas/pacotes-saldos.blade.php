@extends('layouts.app')

@section('title', 'Saldos de Pacotes')
@section('header', 'Gestão de Pacotes & Consumo de Sessões')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Saldos de Pacotes dos Pacientes</h1>
            <p class="text-xs text-slate-500">Acompanhe sessões restantes, consumo em atendimentos e validades.</p>
        </div>
        <a href="{{ route('vendas.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Vender Novo Pacote</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('pacientes-pacotes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar por paciente ou nome do pacote..."
                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
            </div>
            <div class="sm:col-span-4 flex items-center gap-2">
                <select name="status" class="flex-1 text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    <option value="">Todas as situações</option>
                    <option value="ativo" {{ $status === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="concluido" {{ $status === 'concluido' ? 'selected' : '' }}>Concluídos</option>
                    <option value="expirado" {{ $status === 'expirado' ? 'selected' : '' }}>Expirados</option>
                    <option value="pausado" {{ $status === 'pausado' ? 'selected' : '' }}>Pausados</option>
                </select>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Package Balances Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($pacotes as $pct)
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    @php
                        $badges = [
                            'ativo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'concluido' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'expirado' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'pausado' => 'bg-amber-50 text-amber-700 border-amber-200',
                        ];
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badges[$pct->status] ?? 'bg-slate-100' }}">
                        {{ ucfirst($pct->status) }}
                    </span>
                    <span class="text-[11px] text-slate-400">Validade: {{ $pct->data_validade->format('d/m/Y') }}</span>
                </div>

                <div class="mt-2">
                    <a href="{{ route('pacientes.show', $pct->paciente_id) }}" class="font-bold text-slate-900 text-sm hover:text-teal-600 transition block">
                        {{ $pct->paciente->nome }}
                    </a>
                    <span class="text-xs text-slate-500 font-medium">{{ $pct->nome_pacote }}</span>
                </div>

                <!-- Progress Bar -->
                @php
                    $perc = $pct->sessoes_totais > 0 ? round(($pct->sessoes_utilizadas / $pct->sessoes_totais) * 100) : 0;
                @endphp
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                        <span class="text-slate-600">Consumo</span>
                        <span class="text-teal-700">{{ $pct->sessoes_utilizadas }} de {{ $pct->sessoes_totais }} sessões</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-teal-600 h-2.5 rounded-full" style="width: {{ $perc }}%"></div>
                    </div>
                </div>

                <!-- Metrics Box -->
                <div class="grid grid-cols-3 gap-2 text-center mt-3 pt-3 border-t border-slate-100 text-xs">
                    <div class="bg-slate-50 p-2 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase">Totais</span>
                        <span class="font-bold text-slate-800">{{ $pct->sessoes_totais }}</span>
                    </div>
                    <div class="bg-teal-50 p-2 rounded-xl">
                        <span class="text-teal-600 block text-[10px] uppercase">Realizadas</span>
                        <span class="font-bold text-teal-800">{{ $pct->sessoes_utilizadas }}</span>
                    </div>
                    <div class="bg-emerald-50 p-2 rounded-xl">
                        <span class="text-emerald-600 block text-[10px] uppercase">Restantes</span>
                        <span class="font-bold text-emerald-800">{{ $pct->sessoes_restantes }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">
                    @if($pct->sessoes_bonus > 0)
                        +{{ $pct->sessoes_bonus }} bônus
                    @endif
                </span>
                <a href="{{ route('pacientes-pacotes.show', $pct->id) }}" class="text-xs font-bold text-teal-600 hover:underline flex items-center gap-1">
                    <span>Extrato de Sessões</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            <i data-lucide="layers" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
            <p class="text-sm font-medium">Nenhum pacote localizado com os filtros selecionados.</p>
        </div>
        @endforelse
    </div>

    @if($pacotes->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200">
        {{ $pacotes->links() }}
    </div>
    @endif
</div>
@endsection
