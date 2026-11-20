@extends('layouts.app')

@section('title', 'Contas a Receber')
@section('header', 'Contas a Receber')

@section('content')
<div class="space-y-6" x-data="{ modalBaixa: false, contaId: null, valorNominal: 0, descricaoConta: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Contas a Receber</h1>
            <p class="text-xs text-slate-500">Acompanhe recebimentos previstos, baixas de pagamentos e cobranças de pacientes.</p>
        </div>
        <a href="{{ route('vendas.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Lançar Venda / Recebimento</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('financeiro.receber') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar por descrição ou nome do paciente..."
                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
            </div>
            <div class="sm:col-span-4 flex items-center gap-2">
                <select name="status" class="flex-1 text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    <option value="">Todas as situações</option>
                    <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendentes (A Receber)</option>
                    <option value="pago" {{ $status === 'pago' ? 'selected' : '' }}>Recebidos</option>
                    <option value="cancelado" {{ $status === 'cancelado' ? 'selected' : '' }}>Cancelados</option>
                </select>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition">
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
                    <th class="py-3.5 px-4">Descrição</th>
                    <th class="py-3.5 px-4">Paciente</th>
                    <th class="py-3.5 px-4">Vencimento</th>
                    <th class="py-3.5 px-4">Recebimento</th>
                    <th class="py-3.5 px-4">Valor</th>
                    <th class="py-3.5 px-4">Situação</th>
                    <th class="py-3.5 px-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contas as $conta)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        {{ $conta->descricao }}
                        @if($conta->venda)
                            <a href="{{ route('vendas.show', $conta->venda_id) }}" class="text-[10px] text-teal-600 hover:underline block font-normal">
                                Venda: {{ $conta->venda->codigo }}
                            </a>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 font-medium text-slate-700">
                        {{ $conta->paciente->nome ?? 'Avulso' }}
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        {{ $conta->data_vencimento->format('d/m/Y') }}
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        {{ $conta->data_recebimento ? $conta->data_recebimento->format('d/m/Y') : '-' }}
                    </td>
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        R$ {{ number_format($conta->valor_recebido ?: $conta->valor_nominal, 2, ',', '.') }}
                    </td>
                    <td class="py-3.5 px-4">
                        @php $b = $conta->status_badge; @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $b['bg'] }} {{ $b['text'] }}">
                            {{ $b['label'] }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        @if($conta->status === 'pendente')
                        <button type="button" @click="contaId = {{ $conta->id }}; valorNominal = {{ $conta->valor_nominal }}; descricaoConta = '{{ addslashes($conta->descricao) }}'; modalBaixa = true;"
                                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                            Baixar Recebimento
                        </button>
                        @else
                        <span class="text-slate-400 text-[11px]">Concluído</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Nenhum título a receber localizado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($contas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $contas->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: Baixar Recebimento -->
    <div x-show="modalBaixa" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200" @click.outside="modalBaixa = false">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Confirmar Recebimento</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="descricaoConta"></p>

            <form :action="'{{ url('financeiro/receber') }}/' + contaId + '/baixa'" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Recebido (R$) *</label>
                    <input type="number" step="0.01" name="valor_recebido" :value="valorNominal" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data do Recebimento *</label>
                    <input type="date" name="data_recebimento" value="{{ now()->format('Y-m-d') }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Forma de Pagamento</label>
                    <select name="forma_pagamento_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="">Forma padrão</option>
                        @foreach($formasPagamento as $fp)
                            <option value="{{ $fp->id }}">{{ $fp->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalBaixa = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm">
                        Confirmar Baixa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
