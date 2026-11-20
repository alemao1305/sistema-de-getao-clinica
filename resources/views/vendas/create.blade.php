@extends('layouts.app')

@section('title', 'Nova Venda')
@section('header', 'Realizar Venda de Pacote ou Serviço')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    tipoItem: 'pacote',
    itemId: '',
    subtotal: 0,
    desconto: 0,
    sessoesBonus: 0,
    pagamentos: [
        { forma: 'pix', valor: 0, parcelas: 1 }
    ],
    pacotes: {{ Js::from($pacotes) }},
    servicos: {{ Js::from($servicos) }},
    produtos: {{ Js::from($produtos) }},

    init() {
        if (this.pacotes.length > 0) {
            this.itemId = this.pacotes[0].id;
            this.atualizarPreco();
        }
    },

    aoMudarTipo() {
        if (this.tipoItem === 'pacote' && this.pacotes.length > 0) {
            this.itemId = this.pacotes[0].id;
        } else if (this.tipoItem === 'servico' && this.servicos.length > 0) {
            this.itemId = this.servicos[0].id;
        } else if (this.tipoItem === 'produto' && this.produtos.length > 0) {
            this.itemId = this.produtos[0].id;
        } else {
            this.itemId = '';
        }
        this.atualizarPreco();
    },

    atualizarPreco() {
        if (this.tipoItem === 'pacote') {
            const p = this.pacotes.find(x => x.id == this.itemId);
            this.subtotal = p ? parseFloat(p.preco_total) : 0;
        } else if (this.tipoItem === 'servico') {
            const s = this.servicos.find(x => x.id == this.itemId);
            this.subtotal = s ? parseFloat(s.preco_padrao) : 0;
        } else if (this.tipoItem === 'produto') {
            const pr = this.produtos.find(x => x.id == this.itemId);
            this.subtotal = pr ? parseFloat(pr.preco_venda) : 0;
        }
        this.recalcularPagamentos();
    },

    get total() {
        return Math.max(0, this.subtotal - (parseFloat(this.desconto) || 0));
    },

    get totalPago() {
        return this.pagamentos.reduce((acc, curr) => acc + (parseFloat(curr.valor) || 0), 0);
    },

    get restante() {
        return (this.total - this.totalPago).toFixed(2);
    },

    recalcularPagamentos() {
        if (this.pagamentos.length === 1) {
            this.pagamentos[0].valor = this.total.toFixed(2);
        }
    },

    adicionarPagamento() {
        const rest = Math.max(0, parseFloat(this.restante));
        this.pagamentos.push({ forma: 'cartao_credito', valor: rest.toFixed(2), parcelas: 1 });
    },

    removerPagamento(idx) {
        if (this.pagamentos.length > 1) {
            this.pagamentos.splice(idx, 1);
        }
    },

    preencherRestante(idx) {
        const rest = Math.max(0, parseFloat(this.restante));
        this.pagamentos[idx].valor = (parseFloat(this.pagamentos[idx].valor) + rest).toFixed(2);
    }
}">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Nova Venda / Contratação de Pacote</h1>
            <p class="text-xs text-slate-500">Gere saldo de sessões para o paciente e lance recebimentos com divisão de pagamento.</p>
        </div>
        <a href="{{ route('vendas.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar às vendas
        </a>
    </div>

    <form method="POST" action="{{ route('vendas.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Seleção do Paciente e Item -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2">
                <i data-lucide="user-check" class="w-4 h-4 text-teal-600"></i>
                Identificação do Paciente & Item da Venda
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-8">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paciente Comprador *</label>
                    <select name="paciente_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                        <option value="">Selecione o paciente...</option>
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}" {{ $pacienteId == $p->id ? 'selected' : '' }}>
                                {{ $p->nome }} (CPF: {{ $p->cpf_formatado ?? 'Não inf.' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data da Venda *</label>
                    <input type="date" name="data_venda" value="{{ now()->format('Y-m-d') }}" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Item</label>
                    <select name="tipo_item" x-model="tipoItem" @change="aoMudarTipo()" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                        <option value="pacote">Pacote Pré-Pago de Sessões</option>
                        <option value="servico">Consulta / Sessão Avulsa</option>
                        <option value="produto">Produto Fisioterapêutico (Estoque)</option>
                    </select>
                </div>

                <!-- Select Dinâmico Pacote, Serviço ou Produto -->
                <div class="sm:col-span-8">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Item Selecionado *</label>
                    <template x-if="tipoItem === 'pacote'">
                        <select name="item_id" x-model="itemId" @change="atualizarPreco()" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <template x-for="pct in pacotes" :key="pct.id">
                                <option :value="pct.id" x-text="`${pct.nome} (${pct.quantidade_sessoes} sessões - R$ ${parseFloat(pct.preco_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})})`"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="tipoItem === 'servico'">
                        <select name="item_id" x-model="itemId" @change="atualizarPreco()" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <template x-for="srv in servicos" :key="srv.id">
                                <option :value="srv.id" x-text="`${srv.nome} (${srv.duracao_minutos} min - R$ ${parseFloat(srv.preco_padrao).toLocaleString('pt-BR', {minimumFractionDigits: 2})})`"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="tipoItem === 'produto'">
                        <select name="item_id" x-model="itemId" @change="atualizarPreco()" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                            <template x-for="prd in produtos" :key="prd.id">
                                <option :value="prd.id" x-text="`${prd.nome} (${prd.estoque_atual} ${prd.unidade_medida} em estoque - R$ ${parseFloat(prd.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})})`"></option>
                            </template>
                        </select>
                    </template>
                </div>

                <!-- Desconto & Sessões Bônus -->
                <div class="sm:col-span-6" x-show="tipoItem === 'pacote'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sessões Bônus / Cortesia</label>
                    <input type="number" name="sessoes_bonus" min="0" max="10" x-model.number="sessoesBonus"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600" placeholder="0">
                    <span class="text-[10px] text-slate-400">Serão adicionadas ao saldo de sessões do paciente.</span>
                </div>

                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Desconto Autorizado (R$)</label>
                    <input type="number" name="valor_desconto" min="0" step="0.01" x-model="desconto" @input="recalcularPagamentos()"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600" placeholder="0,00">
                </div>
            </div>

            <!-- Totalizador Card -->
            <div class="p-4 bg-teal-50/80 rounded-2xl border border-teal-200/80 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-500 block">Subtotal: R$ <span x-text="parseFloat(subtotal).toFixed(2)"></span></span>
                    <span class="text-slate-500 block" x-show="desconto > 0">Desconto: - R$ <span x-text="parseFloat(desconto).toFixed(2)"></span></span>
                </div>
                <div class="text-right">
                    <span class="text-xs uppercase font-bold text-teal-800 block">Valor Total da Venda</span>
                    <span class="text-2xl font-black text-teal-900 leading-tight">
                        R$ <span x-text="total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </span>
                </div>
            </div>
            <input type="hidden" name="valor_subtotal" :value="subtotal">
        </div>

        <!-- 2. Pagamento Dividido (Split Payment) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-teal-600"></i>
                        Formas de Pagamento (Divisão de Pagamento)
                    </h2>
                    <p class="text-xs text-slate-500">Combine mais de uma forma de pagamento (ex: 50% Pix e 50% Cartão de Crédito).</p>
                </div>
                <button type="button" @click="adicionarPagamento()"
                        class="px-3 py-1.5 rounded-xl border border-teal-600 text-teal-700 hover:bg-teal-50 text-xs font-semibold flex items-center gap-1 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> + Forma de Pagamento
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(pag, idx) in pagamentos" :key="idx">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Forma</label>
                            <select :name="`pagamentos[${idx}][forma]`" x-model="pag.forma" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                                <option value="pix">Pix (À vista)</option>
                                <option value="dinheiro">Dinheiro em Espécie</option>
                                <option value="cartao_debito">Cartão de Débito</option>
                                <option value="cartao_credito">Cartão de Crédito</option>
                                <option value="boleto">Boleto Bancário</option>
                                <option value="transferencia">Transferência / TED</option>
                            </select>
                        </div>

                        <div class="w-full sm:w-40">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Valor (R$) *</label>
                            <input type="number" :name="`pagamentos[${idx}][valor]`" step="0.01" min="0.01" x-model="pag.valor" required
                                   class="w-full text-xs rounded-xl border border-slate-300 p-2 font-bold text-slate-900">
                        </div>

                        <div class="w-full sm:w-28" x-show="pag.forma === 'cartao_credito'">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Parcelas</label>
                            <select :name="`pagamentos[${idx}][parcelas]`" x-model="pag.parcelas" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                                <option value="1">1x (À vista)</option>
                                <option value="2">2x</option>
                                <option value="3">3x</option>
                                <option value="4">4x</option>
                                <option value="6">6x</option>
                                <option value="10">10x</option>
                                <option value="12">12x</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1 self-end sm:self-center sm:pt-4">
                            <button type="button" @click="preencherRestante(idx)" title="Preencher com valor restante"
                                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg text-xs font-semibold">
                                = Restante
                            </button>
                            <button type="button" @click="removerPagamento(idx)" x-show="pagamentos.length > 1"
                                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Balanço de Pagamento -->
            <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs font-semibold"
                 :class="Math.abs(parseFloat(restante)) < 0.05 ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'">
                <div>
                    <span>Total da Venda: R$ <span x-text="total.toFixed(2)"></span></span> &bull;
                    <span>Alocado: R$ <span x-text="totalPago.toFixed(2)"></span></span>
                </div>
                <div>
                    <template x-if="Math.abs(parseFloat(restante)) < 0.05">
                        <span class="flex items-center gap-1 text-emerald-700">
                            <i data-lucide="check" class="w-4 h-4"></i> Valor 100% alocado!
                        </span>
                    </template>
                    <template x-if="Math.abs(parseFloat(restante)) >= 0.05">
                        <span class="text-rose-700">
                            Diferença pendente: R$ <span x-text="restante"></span>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Observações da Venda</label>
            <textarea name="observacoes" rows="2" placeholder="Notas sobre a negociação comercial ou parcelamento..."
                      class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('vendas.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button type="submit" :disabled="Math.abs(parseFloat(restante)) >= 0.05"
                    :class="Math.abs(parseFloat(restante)) < 0.05 ? 'bg-teal-600 hover:bg-teal-700' : 'bg-slate-400 cursor-not-allowed'"
                    class="px-6 py-2.5 rounded-xl text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Confirmar Venda & Creditar Sessões</span>
            </button>
        </div>
    </form>
</div>
@endsection
