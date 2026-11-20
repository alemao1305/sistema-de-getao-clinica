@extends('layouts.app')

@section('title', 'Salas de Atendimento')
@section('header', 'Salas de Atendimento')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    formAction: '{{ route('salas.store') }}',
    sala: {
        id: '',
        nome: '',
        descricao: '',
        capacidade_simultanea: 1,
        cor: '#0d9488',
        ativa: true
    },
    abrirModalCriacao() {
        this.editMode = false;
        this.formAction = '{{ route('salas.store') }}';
        this.sala = {
            id: '',
            nome: '',
            descricao: '',
            capacidade_simultanea: 1,
            cor: '#0d9488',
            ativa: true
        };
        this.modalOpen = true;
    },
    abrirModalEdicao(salaData) {
        this.editMode = true;
        this.formAction = '/configuracoes/salas/' + salaData.id;
        this.sala = { ...salaData };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Salas</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Salas & Boxes de Atendimento</h1>
            <p class="text-xs text-slate-500">Gerencie as salas físicas, capacidade simultânea para evitar conflitos na agenda.</p>
        </div>

        <button @click="abrirModalCriacao()"
                class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nova Sala</span>
        </button>
    </div>

    <!-- Lista de Salas -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Sala / Espaço</th>
                        <th class="py-3 px-4">Capacidade Simultânea</th>
                        <th class="py-3 px-4">Agendamentos Vinculados</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salas as $sala)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" style="background-color: {{ $sala->cor ?? '#0d9488' }};"></span>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">{{ $sala->nome }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $sala->descricao ?? 'Sem observações' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i>
                                Até {{ $sala->capacidade_simultanea }} {{ $sala->capacidade_simultanea > 1 ? 'pacientes' : 'paciente' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $sala->agendamentos_count }} agendamentos
                        </td>
                        <td class="py-3.5 px-4">
                            @if($sala->ativa)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                    Ativa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    Inativa
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="abrirModalEdicao({{ json_encode($sala) }})"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                            Nenhuma sala cadastrada no momento.
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
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="editMode ? 'Editar Sala de Atendimento' : 'Nova Sala de Atendimento'"></h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome da Sala *</label>
                    <input type="text" name="nome" x-model="sala.nome" required placeholder="Ex: Box 1, Sala de Pilates, Consultório A"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Capacidade Simultânea *</label>
                        <input type="number" name="capacidade_simultanea" x-model="sala.capacidade_simultanea" min="1" max="10" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                        <span class="text-[10px] text-slate-400">Pacientes ao mesmo tempo</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cor de Destaque</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="cor" x-model="sala.cor" class="h-9 w-12 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                            <input type="text" x-model="sala.cor" class="w-full text-xs rounded-xl border border-slate-300 p-2 uppercase">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição / Equipamentos</label>
                    <textarea name="descricao" x-model="sala.descricao" rows="2" placeholder="Ex: Maca hidráulica, ultrassom, turbilhão..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500"></textarea>
                </div>

                <template x-if="editMode">
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="ativa" name="ativa" value="1" :checked="sala.ativa" class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="ativa" class="text-xs font-medium text-slate-700">Sala ativa e disponível para agendamentos</label>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Sala
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
