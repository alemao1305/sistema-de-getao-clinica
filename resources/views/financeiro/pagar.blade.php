@extends('layouts.app')

@section('title', 'Contas a Pagar')
@section('header', 'Contas a Pagar & Despesas')

@section('content')
<div class="space-y-6" x-data="{
    modalNovaDespesa: {{ request()->get('novo') ? 'true' : 'false' }},
    modalBaixa: false,
    contaId: null,
    valorNominal: 0,
    descricaoConta: ''
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Contas a Pagar</h1>
            <p class="text-xs text-slate-500">Controle de despesas operacionais, fornecedores e vencimentos da clínica.</p>
        </div>
        <button type="button" @click="modalNovaDespesa = true"
                class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nova Despesa</span>
        </button>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('financeiro.pagar') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar por descrição ou fornecedor..."
                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
            </div>
            <div class="sm:col-span-4 flex items-center gap-2">
                <select name="status" class="flex-1 text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                    <option value="">Todas as situações</option>
                    <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>A Pagar (Pendentes)</option>
                    <option value="pago" {{ $status === 'pago' ? 'selected' : '' }}>Pagas</option>
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
                    <th class="py-3.5 px-4">Descrição / Categoria</th>
                    <th class="py-3.5 px-4">Fornecedor</th>
                    <th class="py-3.5 px-4">Centro de Custo</th>
                    <th class="py-3.5 px-4">Vencimento</th>
                    <th class="py-3.5 px-4">Data Pagamento</th>
                    <th class="py-3.5 px-4">Valor</th>
                    <th class="py-3.5 px-4">Situação</th>
                    <th class="py-3.5 px-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contas as $conta)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3.5 px-4">
                        <span class="font-bold text-slate-900 block">{{ $conta->descricao }}</span>
                        <span class="text-[10px] text-slate-500">{{ $conta->categoria->nome ?? 'Despesa Geral' }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $conta->fornecedor ?: '-' }}</td>
                    <td class="py-3.5 px-4 text-slate-500">{{ $conta->centro_custo }}</td>
                    <td class="py-3.5 px-4 text-slate-600 font-semibold">{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                    <td class="py-3.5 px-4 text-slate-600">{{ $conta->data_pagamento ? $conta->data_pagamento->format('d/m/Y') : '-' }}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                        R$ {{ number_format($conta->valor_pago ?: $conta->valor, 2, ',', '.') }}
                    </td>
                    <td class="py-3.5 px-4">
                        @php $b = $conta->status_badge; @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $b['bg'] }} {{ $b['text'] }}">
                            {{ $b['label'] }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        @if($conta->status === 'pendente')
                        <button type="button" @click="contaId = {{ $conta->id }}; valorNominal = {{ $conta->valor }}; descricaoConta = '{{ addslashes($conta->descricao) }}'; modalBaixa = true;"
                                class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold transition">
                            Pagar Despesa
                        </button>
                        @else
                        <span class="text-slate-400 text-[11px]">Liquidado</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400 font-medium">Nenhuma conta a pagar localizada.</td>
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

    <!-- MODAL: Nova Despesa -->
    <div x-show="modalNovaDespesa" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200" @click.outside="modalNovaDespesa = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="arrow-up-right" class="w-4 h-4 text-rose-600"></i> Lançar Conta a Pagar / Despesa
            </h3>

            <form method="POST" action="{{ route('financeiro.pagar.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição da Despesa *</label>
                    <input type="text" name="descricao" required placeholder="Ex: Conta de Energia Elétrica / Compra de Bandagens Elásticas"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Fornecedor / Favorecido</label>
                        <input type="text" name="fornecedor" placeholder="Ex: Enel / Dental Cremer"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria Financeira</label>
                        <select name="categoria_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Nominal (R$) *</label>
                        <input type="number" step="0.01" min="0.01" name="valor" required placeholder="0,00"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Data de Vencimento *</label>
                        <input type="date" name="data_vencimento" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Centro de Custo</label>
                    <input type="text" name="centro_custo" value="Infraestrutura"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="2" class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalNovaDespesa = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm">
                        Lançar Despesa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Baixar / Pagar Despesa -->
    <div x-show="modalBaixa" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200" @click.outside="modalBaixa = false">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Confirmar Pagamento</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="descricaoConta"></p>

            <form :action="'{{ url('financeiro/pagar') }}/' + contaId + '/baixa'" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Pago (R$) *</label>
                    <input type="number" step="0.01" name="valor_pago" :value="valorNominal" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-bold text-rose-700">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data Efetiva do Pagamento *</label>
                    <input type="date" name="data_pagamento" value="{{ now()->format('Y-m-d') }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalBaixa = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm">
                        Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
