@extends('layouts.app')

@section('title', 'Produtos & Estoque')
@section('header', 'Catálogo de Produtos & Controle de Estoque')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalProdutoOpen: false,
    editMode: false,
    formAction: '{{ route('produtos.store') }}',
    modalAjusteOpen: false,
    ajusteProduto: null,
    ajusteTipo: 'entrada',
    ajusteQtd: 1,
    ajusteMotivo: '',
    produto: {
        id: '',
        nome: '',
        categoria: 'Cinesioterapia',
        codigo_barras: '',
        sku: '',
        preco_custo: '0.00',
        preco_venda: '0.00',
        estoque_atual: 0,
        estoque_minimo: 5,
        unidade_medida: 'UN',
        descricao: '',
        ativo: true
    },
    abrirModalCriacao() {
        this.editMode = false;
        this.formAction = '{{ route('produtos.store') }}';
        this.produto = {
            id: '',
            nome: '',
            categoria: 'Cinesioterapia',
            codigo_barras: '',
            sku: '',
            preco_custo: '0.00',
            preco_venda: '0.00',
            estoque_atual: 0,
            estoque_minimo: 5,
            unidade_medida: 'UN',
            descricao: '',
            ativo: true
        };
        this.modalProdutoOpen = true;
    },
    abrirModalEdicao(p) {
        this.editMode = true;
        this.formAction = '/produtos/' + p.id;
        this.produto = { ...p };
        this.modalProdutoOpen = true;
    },
    abrirModalAjuste(p) {
        this.ajusteProduto = p;
        this.ajusteTipo = 'entrada';
        this.ajusteQtd = 1;
        this.ajusteMotivo = '';
        this.modalAjusteOpen = true;
    }
}">
    <!-- Navegação de Abas do Catálogo Geral -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('servicos.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition text-slate-600 hover:bg-slate-100">
            <i data-lucide="sparkles" class="w-4 h-4 text-slate-500"></i>
            <span>Serviços & Procedimentos</span>
        </a>
        <a href="{{ route('produtos.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition bg-teal-600 text-white shadow-sm">
            <i data-lucide="package" class="w-4 h-4 text-white"></i>
            <span>Produtos & Estoque</span>
        </a>
        <a href="{{ route('pacotes.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition text-slate-600 hover:bg-slate-100">
            <i data-lucide="tags" class="w-4 h-4 text-slate-500"></i>
            <span>Pacotes de Sessões</span>
        </a>
    </div>

    <!-- Header & Botão Novo Produto -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Produtos Fisioterapêuticos & Acessórios</h1>
            <p class="text-xs text-slate-500">Gerencie faixas, fitas kinesio, bolas, bolsas térmicas e controle o estoque físico da clínica.</p>
        </div>

        <button @click="abrirModalCriacao()"
                class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Novo Produto</span>
        </button>
    </div>

    <!-- Cards de Resumo de Estoque -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Total de Itens</p>
                <p class="text-lg font-bold text-slate-900">{{ $totalProdutos }} produtos</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $totalEstoqueBaixo > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center font-bold">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Estoque Baixo / Repor</p>
                <p class="text-lg font-bold {{ $totalEstoqueBaixo > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $totalEstoqueBaixo }} itens</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i data-lucide="boxes" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Custo Total em Estoque</p>
                <p class="text-lg font-bold text-slate-900">R$ {{ number_format($valorTotalEstoqueCusto, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Potencial de Venda</p>
                <p class="text-lg font-bold text-emerald-700">R$ {{ number_format($valorTotalEstoqueVenda, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('produtos.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <label class="block font-semibold text-slate-700 mb-1">Buscar por Nome, SKU ou Código</label>
                <div class="relative">
                    <input type="text" name="busca" value="{{ $busca }}" placeholder="Ex: Theraband, Kinesio, KT-01..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 pl-8 focus:border-teal-500">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-3"></i>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label class="block font-semibold text-slate-700 mb-1">Categoria</label>
                <select name="categoria" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    <option value="">Todas as categorias</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}" @selected($categoria === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Nível de Estoque</label>
                <select name="estoque" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    <option value="">Todos os status</option>
                    <option value="baixo" @selected($filtroEstoque === 'baixo')>Estoque Baixo</option>
                    <option value="zerado" @selected($filtroEstoque === 'zerado')>Zerado / Esgotado</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-sm w-full">
                    Filtrar
                </button>
                @if($busca || $categoria || $filtroEstoque)
                <a href="{{ route('produtos.index') }}" class="p-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition" title="Limpar filtros">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Produtos -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Produto / SKU</th>
                        <th class="py-3 px-4">Categoria</th>
                        <th class="py-3 px-4">Preço Custo</th>
                        <th class="py-3 px-4">Preço Venda</th>
                        <th class="py-3 px-4">Margem</th>
                        <th class="py-3 px-4">Estoque Físico</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($produtos as $prod)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $prod->nome }}</div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-2">
                                @if($prod->sku)
                                    <span>SKU: <strong class="text-slate-600 font-mono">{{ $prod->sku }}</strong></span>
                                @endif
                                @if($prod->codigo_barras)
                                    <span>EAN: <strong class="text-slate-600 font-mono">{{ $prod->codigo_barras }}</strong></span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-600">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] bg-slate-100 text-slate-700">
                                {{ $prod->categoria }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            R$ {{ number_format($prod->preco_custo, 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            R$ {{ number_format($prod->preco_venda, 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-emerald-700 font-bold">
                            +{{ $prod->margem_lucro_percentual }}%
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm {{ $prod->estoque_atual <= $prod->estoque_minimo ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $prod->estoque_atual }} {{ $prod->unidade_medida }}
                                </span>
                                @if($prod->estoque_atual <= $prod->estoque_minimo)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800" title="Abaixo do mínimo de {{ $prod->estoque_minimo }}">
                                        Baixo
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] text-slate-400">Mínimo: {{ $prod->estoque_minimo }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($prod->ativo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    Inativo
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Ajustar Estoque -->
                                <button type="button" @click="abrirModalAjuste({{ json_encode($prod) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-teal-50 transition" title="Movimentar / Ajustar Estoque">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>

                                <!-- Editar -->
                                <button type="button" @click="abrirModalEdicao({{ json_encode($prod) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar Produto">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>

                                <!-- Ativar/Inativar -->
                                <form method="POST" action="{{ route('produtos.toggle-status', $prod->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                                            title="{{ $prod->ativo ? 'Inativar Produto' : 'Ativar Produto' }}">
                                        <i data-lucide="{{ $prod->ativo ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                            Nenhum produto encontrado no catálogo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($produtos->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $produtos->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL 1: Cadastro / Edição de Produto -->
    <div x-show="modalProdutoOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalProdutoOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="editMode ? 'Editar Dados do Produto' : 'Novo Produto Fisioterapêutico'"></h3>
                <button @click="modalProdutoOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome do Produto *</label>
                    <input type="text" name="nome" x-model="produto.nome" required placeholder="Ex: Faixa Elástica Theraband Nível Médio (Verde)"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria *</label>
                        <input type="text" name="categoria" x-model="produto.categoria" required placeholder="Ex: Cinesioterapia, Órteses, Bandagens"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Unidade de Medida *</label>
                        <select name="unidade_medida" x-model="produto.unidade_medida" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="UN">Unidade (UN)</option>
                            <option value="CX">Caixa (CX)</option>
                            <option value="RL">Rolo (RL)</option>
                            <option value="PAR">Par (PAR)</option>
                            <option value="KIT">Kit (KIT)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Código SKU</label>
                        <input type="text" name="sku" x-model="produto.sku" placeholder="Ex: TB-VD-01"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-mono uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Código de Barras (EAN)</label>
                        <input type="text" name="codigo_barras" x-model="produto.codigo_barras" placeholder="Ex: 7891234567890"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Preço de Custo (R$) *</label>
                        <input type="number" step="0.01" name="preco_custo" x-model="produto.preco_custo" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Preço de Venda (R$) *</label>
                        <input type="number" step="0.01" name="preco_venda" x-model="produto.preco_venda" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-bold text-teal-700">
                    </div>

                    <div x-show="!editMode">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estoque Inicial *</label>
                        <input type="number" name="estoque_atual" x-model="produto.estoque_atual" min="0" :required="!editMode"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estoque Mínimo de Alerta *</label>
                        <input type="number" name="estoque_minimo" x-model="produto.estoque_minimo" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <span class="text-[10px] text-slate-400">Avisa quando atingir esta quantidade</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição / Instruções de Uso</label>
                    <textarea name="descricao" x-model="produto.descricao" rows="2" placeholder="Especificações técnicas, tamanho, indicação clínica..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalProdutoOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Movimentação / Ajuste de Estoque -->
    <div x-show="modalAjusteOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalAjusteOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Movimentar Estoque</h3>
                    <p class="text-xs text-slate-500" x-text="ajusteProduto?.nome"></p>
                </div>
                <button @click="modalAjusteOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="'/produtos/' + ajusteProduto?.id + '/ajustar-estoque'" method="POST" class="space-y-4">
                @csrf

                <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                    <span class="text-slate-600">Saldo Atual em Estoque:</span>
                    <strong class="text-base text-slate-900" x-text="(ajusteProduto?.estoque_atual || 0) + ' ' + (ajusteProduto?.unidade_medida || 'UN')"></strong>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Operação *</label>
                    <select name="tipo" x-model="ajusteTipo" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="entrada">Entrada (+ Compra / Chegada de Fornecedor)</option>
                        <option value="saida">Saída (- Perda, Quebra ou Uso Interno)</option>
                        <option value="ajuste">Ajuste Balanço (Definir quantidade exata contada)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantidade *</label>
                    <input type="number" name="quantidade" x-model="ajusteQtd" min="1" required
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Justificativa / Motivo *</label>
                    <input type="text" name="motivo" x-model="ajusteMotivo" required placeholder="Ex: NF 1234 de compra, contagem física de estoque..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalAjusteOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Confirmar Movimentação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
