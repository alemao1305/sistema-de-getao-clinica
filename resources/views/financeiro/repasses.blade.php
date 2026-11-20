@extends('layouts.app')

@section('title', 'Repasses Profissionais')
@section('header', 'Repasses & Comissões de Fisioterapeutas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Repasses aos Fisioterapeutas</h1>
            <p class="text-xs text-slate-500">Controle automático de comissões calculadas por atendimentos concluídos.</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Repasses Pendentes</span>
            <div class="text-2xl font-bold text-amber-700 mt-1">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Aguardando liquidação</span>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Repasses Pagos no Período</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Total já transferido</span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('financeiro.repasses') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <select name="profissional_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    <option value="">Todos os profissionais</option>
                    @foreach($profissionais as $p)
                        <option value="{{ $p->id }}" {{ (string)$profissionalId === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-4">
                <select name="status" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    <option value="">Todas as situações</option>
                    <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                    <option value="pago" {{ $status === 'pago' ? 'selected' : '' }}>Pagos</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                    <th class="py-3.5 px-4">Profissional</th>
                    <th class="py-3.5 px-4">Atendimento / Descrição</th>
                    <th class="py-3.5 px-4">Competência</th>
                    <th class="py-3.5 px-4">Base de Cálculo</th>
                    <th class="py-3.5 px-4">Comissão</th>
                    <th class="py-3.5 px-4">Valor a Repassar</th>
                    <th class="py-3.5 px-4">Situação</th>
                    <th class="py-3.5 px-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($repasses as $repasse)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        {{ $repasse->profissional->name }}
                        <span class="block text-[10px] text-slate-400 font-normal">{{ $repasse->profissional->crefito ?? '' }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-700">
                        <span class="font-medium block">{{ $repasse->descricao }}</span>
                        @if($repasse->agendamento)
                            <span class="text-[10px] text-slate-400">Paciente: {{ $repasse->agendamento->paciente->nome }}</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">{{ $repasse->data_competencia->format('d/m/Y') }}</td>
                    <td class="py-3.5 px-4 text-slate-600">R$ {{ number_format($repasse->valor_base_servico, 2, ',', '.') }}</td>
                    <td class="py-3.5 px-4 text-slate-600">
                        @if($repasse->percentual_comissao > 0)
                            {{ $repasse->percentual_comissao }}%
                        @endif
                        @if($repasse->valor_fixo > 0)
                            + R$ {{ number_format($repasse->valor_fixo, 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                        R$ {{ number_format($repasse->valor_repasse, 2, ',', '.') }}
                    </td>
                    <td class="py-3.5 px-4">
                        @if($repasse->status === 'pago')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Pago em {{ $repasse->data_pagamento?->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Pendente
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        @if($repasse->status === 'pendente')
                        <form method="POST" action="{{ route('financeiro.repasses.pagar', $repasse->id) }}" onsubmit="return confirm('Confirmar pagamento deste repasse profissional?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold transition">
                                Liquidar Repasse
                            </button>
                        </form>
                        @else
                        <span class="text-slate-400 text-[11px]">Liquidado</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400 font-medium">Nenhum repasse profissional registrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($repasses->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $repasses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
