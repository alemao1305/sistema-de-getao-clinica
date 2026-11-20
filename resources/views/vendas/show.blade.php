@extends('layouts.app')

@section('title', 'Venda ' . $venda->codigo)
@section('header', 'Comprovante de Venda')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Venda {{ $venda->codigo }}</h1>
            <p class="text-xs text-slate-500">Realizada em {{ $venda->data_venda->format('d/m/Y') }} por {{ $venda->vendedor->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition">
                <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Recibo
            </button>
            <a href="{{ route('vendas.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 space-y-6">
        <!-- Clinic & Receipt Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Clínica Fabris</h2>
                    <p class="text-xs text-slate-500">Fisioterapia e Reabilitação Especializada</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase font-bold text-slate-400 block">Recibo de Venda</span>
                <span class="text-sm font-bold text-teal-700">{{ $venda->codigo }}</span>
            </div>
        </div>

        <!-- Paciente Info -->
        <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl">
            <div>
                <span class="text-slate-500 block">Paciente Comprador:</span>
                <a href="{{ route('pacientes.show', $venda->paciente_id) }}" class="font-bold text-slate-900 text-sm hover:underline">
                    {{ $venda->paciente->nome }}
                </a>
                <span class="text-slate-500 text-[11px] block mt-0.5">CPF: {{ $venda->paciente->cpf_formatado ?? 'Não informado' }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-500 block">Data de Emissão:</span>
                <span class="font-bold text-slate-800">{{ $venda->data_venda->format('d/m/Y') }}</span>
                <span class="text-slate-500 text-[11px] block mt-0.5">Responsável: {{ $venda->vendedor->name }}</span>
            </div>
        </div>

        <!-- Itens da Venda -->
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Itens Contratados</h3>
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 font-bold">
                        <th class="py-2">Item / Descrição</th>
                        <th class="py-2 text-center">Qtd</th>
                        <th class="py-2 text-right">Valor Unitário</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($venda->itens as $item)
                    <tr>
                        <td class="py-3 font-semibold text-slate-800">{{ $item->descricao }}</td>
                        <td class="py-3 text-center">{{ $item->quantidade }}</td>
                        <td class="py-3 text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="py-3 text-right font-bold text-slate-900">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totalizadores -->
        <div class="pt-2 border-t border-slate-100 flex justify-end">
            <div class="w-64 space-y-1.5 text-xs text-right">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span>R$ {{ number_format($venda->valor_subtotal, 2, ',', '.') }}</span>
                </div>
                @if($venda->valor_desconto > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Desconto:</span>
                    <span>- R$ {{ number_format($venda->valor_desconto, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Pago:</span>
                    <span class="text-teal-700">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Pagamentos Realizados (Split) -->
        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Discriminação do Pagamento</h3>
            <div class="space-y-2">
                @foreach($venda->pagamentos as $pag)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800">
                        {{ $pag->forma_pagamento_label }}
                        @if($pag->parcelas > 1) ({{ $pag->parcelas }}x) @endif
                    </span>
                    <span class="font-bold text-teal-800">R$ {{ number_format($pag->valor, 2, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Saldo de Pacote Vinculado -->
        @if($venda->pacotesGerados->isNotEmpty())
        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Crédito de Sessões Gerado</h3>
            @foreach($venda->pacotesGerados as $pct)
            <div class="p-4 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-teal-950 block text-sm">{{ $pct->nome_pacote }}</span>
                    <span class="text-teal-700">
                        Total de {{ $pct->sessoes_totais }} sessões liberadas &bull; Validade até {{ $pct->data_validade->format('d/m/Y') }}
                    </span>
                </div>
                <a href="{{ route('pacientes-pacotes.show', $pct->id) }}" class="px-3 py-1.5 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition">
                    Extrato de Sessões &rarr;
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
