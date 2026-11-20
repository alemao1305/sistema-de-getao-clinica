@extends('layouts.app')

@section('title', 'Fluxo de Caixa')
@section('header', 'Gestão Financeira & Fluxo de Caixa')

@section('content')
<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Visão Geral do Caixa</h1>
            <p class="text-xs text-slate-500">Acompanhe receitas, despesas operacionais e repasses aos profissionais.</p>
        </div>

        <!-- Month Selector Form -->
        <form method="GET" action="{{ route('financeiro.index') }}" class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-600">Competência:</label>
            <input type="month" name="mes_ano" value="{{ $mesAno }}" onchange="this.form.submit()"
                   class="text-xs font-semibold rounded-xl border border-slate-300 py-1.5 px-3 focus:ring-teal-600">
        </form>
    </div>

    <!-- Main Indicators Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Receitas Realizadas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Receitas Entradas</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-emerald-700">R$ {{ number_format($receitasRealizadas, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">
                A receber no mês: R$ {{ number_format($receitasPrevistas, 2, ',', '.') }}
            </div>
        </div>

        <!-- Despesas Operacionais Pagas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Despesas Pagas</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-rose-700">R$ {{ number_format($despesasRealizadas, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">
                A pagar no mês: R$ {{ number_format($despesasPrevistas, 2, ',', '.') }}
            </div>
        </div>

        <!-- Repasses a Fisioterapeutas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Repasses Pagos</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600"><i data-lucide="user-check" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold text-purple-700">R$ {{ number_format($repassesPagos, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">
                Pendentes: R$ {{ number_format($repassesPendentes, 2, ',', '.') }}
            </div>
        </div>

        <!-- Saldo Líquido do Mês -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Saldo Líquido</span>
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600"><i data-lucide="wallet" class="w-4 h-4"></i></span>
            </div>
            <div class="text-2xl font-bold {{ $saldoRealizado >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                R$ {{ number_format($saldoRealizado, 2, ',', '.') }}
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Receitas - (Despesas + Repasses)
            </div>
        </div>
    </div>

    <!-- Quick Navigation to Financial Sub-modules -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('financeiro.receber') }}"
           class="p-4 bg-white hover:bg-emerald-50/50 rounded-2xl shadow-sm border border-slate-200 hover:border-emerald-300 transition flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contas a Receber</h3>
                    <p class="text-xs text-slate-500">Gestão de recebimentos e vendas</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
        </a>

        <a href="{{ route('financeiro.pagar') }}"
           class="p-4 bg-white hover:bg-rose-50/50 rounded-2xl shadow-sm border border-slate-200 hover:border-rose-300 transition flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contas a Pagar</h3>
                    <p class="text-xs text-slate-500">Despesas e custos operacionais</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
        </a>

        <a href="{{ route('financeiro.repasses') }}"
           class="p-4 bg-white hover:bg-purple-50/50 rounded-2xl shadow-sm border border-slate-200 hover:border-purple-300 transition flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Repasses Fisioterapeutas</h3>
                    <p class="text-xs text-slate-500">Comissões e repasses de atendimentos</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400"></i>
        </a>
    </div>

    <!-- Recent Cash Flow Movements (2 Columns: Receipts vs Payments) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Últimos Recebimentos -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="arrow-down-left" class="w-4 h-4 text-emerald-600"></i> Últimos Recebimentos
                </h3>
                <a href="{{ route('financeiro.receber') }}" class="text-xs font-semibold text-teal-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($ultimosRecebimentos as $rec)
                <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                    <div>
                        <span class="font-bold text-slate-800 block">{{ $rec->descricao }}</span>
                        <span class="text-slate-400 text-[11px]">
                            {{ $rec->paciente->nome ?? 'Avulso' }} &bull; {{ $rec->data_recebimento ? $rec->data_recebimento->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <span class="font-bold text-emerald-700 text-sm">
                        + R$ {{ number_format($rec->valor_recebido ?: $rec->valor_nominal, 2, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="p-6 text-center text-slate-400">Nenhum recebimento realizado recente.</p>
                @endforelse
            </div>
        </div>

        <!-- Últimos Pagamentos de Despesas -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-rose-600"></i> Últimos Pagamentos Efetuados
                </h3>
                <a href="{{ route('financeiro.pagar') }}" class="text-xs font-semibold text-teal-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($ultimosPagamentos as $pag)
                <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                    <div>
                        <span class="font-bold text-slate-800 block">{{ $pag->descricao }}</span>
                        <span class="text-slate-400 text-[11px]">
                            {{ $pag->fornecedor ?: 'Geral' }} &bull; {{ $pag->data_pagamento ? $pag->data_pagamento->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <span class="font-bold text-rose-700 text-sm">
                        - R$ {{ number_format($pag->valor_pago ?: $pag->valor, 2, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="p-6 text-center text-slate-400">Nenhum pagamento efetuado recente.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
