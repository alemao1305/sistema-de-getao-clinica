@extends('layouts.app')

@section('title', 'Catálogo de Serviços')
@section('header', 'Catálogo de Serviços')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    formAction: '{{ route('servicos.store') }}',
    servico: {
        id: '',
        nome: '',
        descricao: '',
        categoria: 'Fisioterapia Traumato-Ortopédica',
        duracao_minutos: 50,
        preco_padrao: '150.00',
        tipo: 'avulso',
        comissao_padrao_percentual: '40.00',
        comissao_padrao_fixo: '',
        ativo: true
    },
    abrirModalCriacao() {
        this.editMode = false;
        this.formAction = '{{ route('servicos.store') }}';
        this.servico = {
            id: '',
            nome: '',
            descricao: '',
            categoria: 'Fisioterapia Traumato-Ortopédica',
            duracao_minutos: 50,
            preco_padrao: '150.00',
            tipo: 'avulso',
            comissao_padrao_percentual: '40.00',
            comissao_padrao_fixo: '',
            ativo: true
        };
        this.modalOpen = true;
    },
    abrirModalEdicao(s) {
        this.editMode = true;
        this.formAction = '/configuracoes/servicos/' + s.id;
        this.servico = { ...s };
        this.modalOpen = true;
    }
}">
    <!-- Navegação de Abas do Catálogo Geral -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('servicos.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition bg-teal-600 text-white shadow-sm">
            <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
            <span>Serviços & Procedimentos</span>
        </a>
        <a href="{{ route('produtos.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition text-slate-600 hover:bg-slate-100">
            <i data-lucide="package" class="w-4 h-4 text-slate-500"></i>
            <span>Produtos & Estoque</span>
        </a>
        <a href="{{ route('pacotes.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition text-slate-600 hover:bg-slate-100">
            <i data-lucide="tags" class="w-4 h-4 text-slate-500"></i>
            <span>Pacotes de Sessões</span>
        </a>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Serviços</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Catálogo de Procedimentos & Serviços</h1>
            <p class="text-xs text-slate-500">Configure os serviços prestados pela clínica, tempo estimado de sessão, preço base e comissões de repasse.</p>
        </div>

        <button @click="abrirModalCriacao()"
                class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Novo Serviço</span>
        </button>
    </div>

    <!-- Tabela de Serviços -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Procedimento</th>
                        <th class="py-3 px-4">Categoria</th>
                        <th class="py-3 px-4">Duração</th>
                        <th class="py-3 px-4">Valor Base</th>
                        <th class="py-3 px-4">Repasse Padrão</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($servicos as $servico)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $servico->nome }}</div>
                            <div class="text-[11px] text-slate-500">{{ Str::limit($servico->descricao, 60) ?? 'Sem descrição' }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-600">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] bg-slate-100 text-slate-700">
                                {{ $servico->categoria }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">
                            {{ $servico->duracao_minutos }} min
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            R$ {{ number_format($servico->preco_padrao, 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-purple-700 font-semibold">
                            @if($servico->comissao_padrao_percentual > 0)
                                {{ number_format($servico->comissao_padrao_percentual, 1, ',', '.') }}%
                            @elseif($servico->comissao_padrao_fixo > 0)
                                R$ {{ number_format($servico->comissao_padrao_fixo, 2, ',', '.') }}
                            @else
                                <span class="text-slate-400 font-normal">Sem comissão</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($servico->ativo)
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
                            <button @click="abrirModalEdicao({{ json_encode($servico) }})"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                            Nenhum serviço cadastrado no catálogo.
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
                <h3 class="text-sm font-bold text-slate-900" x-text="editMode ? 'Editar Serviço' : 'Novo Serviço'"></h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome do Procedimento *</label>
                    <input type="text" name="nome" x-model="servico.nome" required placeholder="Ex: Sessão de Fisioterapia Traumato-Ortopédica"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria *</label>
                        <input type="text" name="categoria" x-model="servico.categoria" required placeholder="Ex: Traumato, RPG, Pilates, Neuro..."
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Serviço *</label>
                        <select name="tipo" x-model="servico.tipo" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="avulso">Avulso</option>
                            <option value="pacote">Pacote / Sessão de pacote</option>
                            <option value="plano">Plano / Mensalidade</option>
                            <option value="convenio">Convênio</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Duração (Minutos) *</label>
                        <input type="number" name="duracao_minutos" x-model="servico.duracao_minutos" min="15" max="240" step="5" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Preço Padrão (R$) *</label>
                        <input type="number" step="0.01" name="preco_padrao" x-model="servico.preco_padrao" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Repasse Padrão (%)</label>
                        <input type="number" step="0.01" name="comissao_padrao_percentual" x-model="servico.comissao_padrao_percentual" min="0" max="100" placeholder="Ex: 40.00"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <span class="text-[10px] text-slate-400">Percentual do valor ao profissional</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Repasse Fixo (R$)</label>
                        <input type="number" step="0.01" name="comissao_padrao_fixo" x-model="servico.comissao_padrao_fixo" min="0" placeholder="Ex: 50.00"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <span class="text-[10px] text-slate-400">Usado se percentual estiver vazio</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição</label>
                    <textarea name="descricao" x-model="servico.descricao" rows="2" placeholder="Orientações e indicações do serviço..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <template x-if="editMode">
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="ativo" name="ativo" value="1" :checked="servico.ativo" class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="ativo" class="text-xs font-medium text-slate-700">Serviço ativo para agendamento e venda</label>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Serviço
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
