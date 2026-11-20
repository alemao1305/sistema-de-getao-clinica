@extends('layouts.app')

@section('title', 'Pacotes Comerciais')
@section('header', 'Pacotes Comerciais')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    formAction: '{{ route('pacotes.store') }}',
    pacote: {
        id: '',
        servico_id: '{{ $servicos->first()->id ?? '' }}',
        nome: '',
        descricao: '',
        quantidade_sessoes: 10,
        preco_total: '1200.00',
        validade_dias: 90,
        regras_utilizacao: '',
        permite_parcelamento: true,
        max_parcelas: 6,
        ativo: true
    },
    abrirModalCriacao() {
        this.editMode = false;
        this.formAction = '{{ route('pacotes.store') }}';
        this.pacote = {
            id: '',
            servico_id: '{{ $servicos->first()->id ?? '' }}',
            nome: '',
            descricao: '',
            quantidade_sessoes: 10,
            preco_total: '1200.00',
            validade_dias: 90,
            regras_utilizacao: '',
            permite_parcelamento: true,
            max_parcelas: 6,
            ativo: true
        };
        this.modalOpen = true;
    },
    abrirModalEdicao(p) {
        this.editMode = true;
        this.formAction = '/configuracoes/pacotes/' + p.id;
        this.pacote = { ...p };
        this.modalOpen = true;
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
           class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition text-slate-600 hover:bg-slate-100">
            <i data-lucide="package" class="w-4 h-4 text-slate-500"></i>
            <span>Produtos & Estoque</span>
        </a>
        <a href="{{ route('pacotes.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition bg-teal-600 text-white shadow-sm">
            <i data-lucide="tags" class="w-4 h-4 text-white"></i>
            <span>Pacotes de Sessões</span>
        </a>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Pacotes Comerciais</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Planos & Pacotes de Sessões</h1>
            <p class="text-xs text-slate-500">Defina os modelos comerciais de pacotes (ex: 10 sessões com desconto), limites de parcelamento e prazos de validade.</p>
        </div>

        <button @click="abrirModalCriacao()"
                class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Novo Pacote</span>
        </button>
    </div>

    <!-- Tabela de Pacotes -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Nome do Pacote</th>
                        <th class="py-3 px-4">Serviço Base</th>
                        <th class="py-3 px-4">Sessões</th>
                        <th class="py-3 px-4">Valor Total</th>
                        <th class="py-3 px-4">Valor / Sessão</th>
                        <th class="py-3 px-4">Validade</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pacotes as $pacote)
                    @php
                        $valorSessao = $pacote->quantidade_sessoes > 0 ? $pacote->preco_total / $pacote->quantidade_sessoes : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $pacote->nome }}</div>
                            <div class="text-[11px] text-slate-500">{{ Str::limit($pacote->descricao, 50) ?? 'Sem observações' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            {{ $pacote->servico->nome ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-teal-700">
                            {{ $pacote->quantidade_sessoes }} sessões
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            R$ {{ number_format($pacote->preco_total, 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            R$ {{ number_format($valorSessao, 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $pacote->validade_dias }} dias
                        </td>
                        <td class="py-3.5 px-4">
                            @if($pacote->ativo)
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
                            <button @click="abrirModalEdicao({{ json_encode($pacote) }})"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500 text-xs">
                            Nenhum pacote comercial cadastrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Cadastro / Edição -->
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="editMode ? 'Editar Pacote Comercial' : 'Novo Pacote Comercial'"></h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome do Pacote *</label>
                    <input type="text" name="nome" x-model="pacote.nome" required placeholder="Ex: Pacote 10 Sessões Fisioterapia Traumato"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Serviço Vinculado *</label>
                        <select name="servico_id" x-model="pacote.servico_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            @foreach($servicos as $servico)
                                <option value="{{ $servico->id }}">{{ $servico->nome }} ({{ $servico->categoria }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quantidade de Sessões *</label>
                        <input type="number" name="quantidade_sessoes" x-model="pacote.quantidade_sessoes" min="2" max="100" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Preço Total do Pacote (R$) *</label>
                        <input type="number" step="0.01" name="preco_total" x-model="pacote.preco_total" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Validade (Dias) *</label>
                        <input type="number" name="validade_dias" x-model="pacote.validade_dias" min="1" max="730" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <span class="text-[10px] text-slate-400">Ex: 90 dias (3 meses), 180 dias</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Máximo de Parcelas *</label>
                        <input type="number" name="max_parcelas" x-model="pacote.max_parcelas" min="1" max="12" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="permite_parcelamento" value="1" :checked="pacote.permite_parcelamento" class="rounded text-teal-600 focus:ring-teal-500">
                            <span class="text-xs font-medium text-slate-700">Permite parcelamento no cartão</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Regras de Utilização / Cancelamento</label>
                    <textarea name="regras_utilizacao" x-model="pacote.regras_utilizacao" rows="2" placeholder="Ex: Cancelamentos avisados com até 2h de antecedência..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <template x-if="editMode">
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="ativo" name="ativo" value="1" :checked="pacote.ativo" class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="ativo" class="text-xs font-medium text-slate-700">Pacote ativo para novas vendas</label>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Pacote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
