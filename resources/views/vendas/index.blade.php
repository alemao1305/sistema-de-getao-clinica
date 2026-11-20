@extends('layouts.app')

@section('title', 'Vendas')
@section('header', 'Vendas & Planos Comerciais')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Vendas de Serviços & Pacotes</h1>
            <p class="text-xs text-slate-500">Histórico de vendas, emissão de recibos e geração de saldo de sessões.</p>
        </div>
        <a href="{{ route('vendas.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
            <span>Nova Venda</span>
        </a>
    </div>

    <!-- Search / Filter -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('vendas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar por código (ex: VND-2026-0001) ou nome do paciente..."
                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
            </div>
            <div class="sm:col-span-4 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition">
                    Filtrar
                </button>
                @if($busca || $status)
                <a href="{{ route('vendas.index') }}" class="p-2.5 text-slate-500 hover:bg-slate-50 border border-slate-200 rounded-xl">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                    <th class="py-3.5 px-4">Código / Data</th>
                    <th class="py-3.5 px-4">Paciente</th>
                    <th class="py-3.5 px-4">Itens Adquiridos</th>
                    <th class="py-3.5 px-4">Pagamento</th>
                    <th class="py-3.5 px-4">Total</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Comprovante</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($vendas as $venda)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        <a href="{{ route('vendas.show', $venda->id) }}" class="text-teal-700 hover:underline">
                            {{ $venda->codigo }}
                        </a>
                        <span class="block text-[10px] text-slate-400 font-normal">{{ $venda->data_venda->format('d/m/Y') }}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <a href="{{ route('pacientes.show', $venda->paciente_id) }}" class="font-bold text-slate-900 hover:text-teal-600 transition">
                            {{ $venda->paciente->nome }}
                        </a>
                    </td>
                    <td class="py-3.5 px-4 text-slate-700">
                        @foreach($venda->itens as $item)
                            <div class="font-medium">{{ $item->descricao }}</div>
                        @endforeach
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        @foreach($venda->pagamentos as $pag)
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-[10px] mr-1">
                                {{ $pag->forma_pagamento_label }} (R$ {{ number_format($pag->valor, 2, ',', '.') }})
                            </span>
                        @endforeach
                    </td>
                    <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                        R$ {{ number_format($venda->valor_total, 2, ',', '.') }}
                        @if($venda->valor_desconto > 0)
                            <span class="block text-[10px] text-emerald-600 font-normal">Desc: R$ {{ number_format($venda->valor_desconto, 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ ucfirst($venda->status) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <a href="{{ route('vendas.show', $venda->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-semibold text-slate-700 transition">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-teal-600"></i>
                            <span>Ver Recibo</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Nenhuma venda registrada até o momento.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($vendas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $vendas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
