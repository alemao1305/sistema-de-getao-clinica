@extends('layouts.app')

@section('title', 'Usuários & Perfis')
@section('header', 'Gestão de Usuários & Acessos')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    formAction: '{{ route('usuarios.store') }}',
    user: {
        id: '',
        name: '',
        email: '',
        role: 'fisioterapeuta',
        phone: '',
        cpf: '',
        crefito: '',
        especialidade: '',
        color: '#0d9488',
        is_active: true
    },
    abrirModalCriacao() {
        this.editMode = false;
        this.formAction = '{{ route('usuarios.store') }}';
        this.user = {
            id: '',
            name: '',
            email: '',
            role: 'fisioterapeuta',
            phone: '',
            cpf: '',
            crefito: '',
            especialidade: '',
            color: '#0d9488',
            is_active: true
        };
        this.modalOpen = true;
    },
    abrirModalEdicao(u) {
        this.editMode = true;
        this.formAction = '/configuracoes/usuarios/' + u.id;
        this.user = { ...u, new_password: '' };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Usuários & Acessos</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Usuários & Profissionais da Clínica</h1>
            <p class="text-xs text-slate-500">Cadastre recepcionistas, fisioterapeutas, gestores e gerencie suas credenciais e perfis de permissão.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('usuarios.permissoes') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="shield-check" class="w-4 h-4 text-teal-600"></i>
                <span>Matriz de Permissões</span>
            </a>

            <button @click="abrirModalCriacao()"
                    class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Novo Usuário</span>
            </button>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Usuário / Nome</th>
                        <th class="py-3 px-4">Perfil de Acesso</th>
                        <th class="py-3 px-4">CREFITO / Especialidade</th>
                        <th class="py-3 px-4">Contato / E-mail</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-xs shadow-sm"
                                     style="background-color: {{ $item->color ?? '#0f766e' }};">
                                    {{ strtoupper(substr($item->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                        {{ $item->name }}
                                        @if($item->id === auth()->id())
                                            <span class="text-[10px] bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded-full">(Você)</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400">CPF: {{ $item->cpf ?? 'Não informado' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                @if($item->role === 'proprietaria') bg-purple-100 text-purple-800
                                @elseif($item->role === 'fisioterapeuta') bg-teal-100 text-teal-800
                                @elseif($item->role === 'financeiro') bg-amber-100 text-amber-800
                                @elseif($item->role === 'recepcao') bg-blue-100 text-blue-800
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $item->role_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @if($item->crefito)
                                <div class="font-semibold text-slate-800">{{ $item->crefito }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item->especialidade ?? 'Geral' }}</div>
                            @else
                                <span class="text-slate-400 italic">Não se aplica</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <div class="font-medium text-slate-800">{{ $item->email }}</div>
                            <div class="text-[11px] text-slate-500">{{ $item->phone ?? 'Sem telefone' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($item->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                    Desativado
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="abrirModalEdicao({{ json_encode($item) }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>

                                @if($item->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.toggle-status', $item->id) }}" class="inline"
                                      onsubmit="return confirm('Deseja {{ $item->is_active ? 'desativar' : 'reativar' }} o acesso deste usuário?');">
                                    @csrf
                                    <button type="submit"
                                            class="p-1.5 rounded-lg text-slate-400 {{ $item->is_active ? 'hover:text-rose-600 hover:bg-rose-50' : 'hover:text-emerald-600 hover:bg-emerald-50' }} transition"
                                            title="{{ $item->is_active ? 'Desativar usuário' : 'Ativar usuário' }}">
                                        <i data-lucide="{{ $item->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                            Nenhum usuário cadastrado.
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
                <h3 class="text-sm font-bold text-slate-900" x-text="editMode ? 'Editar Dados do Usuário' : 'Novo Usuário da Clínica'"></h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo *</label>
                        <input type="text" name="name" x-model="user.name" required placeholder="Ex: Dra. Mariana Costa"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail de Acesso *</label>
                        <input type="email" name="email" x-model="user.email" required placeholder="mariana@clinicafabris.com.br"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Perfil de Acesso *</label>
                        <select name="role" x-model="user.role" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="proprietaria">Proprietária / Administradora</option>
                            <option value="recepcao">Recepção</option>
                            <option value="fisioterapeuta">Fisioterapeuta</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="paciente">Paciente (Portal)</option>
                        </select>
                    </div>

                    <div x-show="!editMode">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Senha Provisória *</label>
                        <input type="password" name="password" :required="!editMode" placeholder="Mínimo 6 caracteres"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                    </div>

                    <div x-show="editMode">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nova Senha (opcional)</label>
                        <input type="password" name="new_password" placeholder="Deixe em branco para manter"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone / WhatsApp</label>
                        <input type="text" name="phone" x-model="user.phone" placeholder="(11) 99999-9999"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">CPF</label>
                        <input type="text" name="cpf" x-model="user.cpf" placeholder="000.000.000-00"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cor na Agenda</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color" x-model="user.color" class="h-9 w-12 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                            <input type="text" x-model="user.color" class="w-full text-xs rounded-xl border border-slate-300 p-2 uppercase">
                        </div>
                    </div>

                    <!-- Campos Profissionais de Fisioterapeuta -->
                    <div class="sm:col-span-2 pt-2 border-t border-slate-100" x-show="user.role === 'fisioterapeuta' || user.role === 'proprietaria'">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Dados do Conselho Regional</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Número CREFITO</label>
                                <input type="text" name="crefito" x-model="user.crefito" placeholder="Ex: CREFITO-3/123456-F"
                                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Especialidade / Foco</label>
                                <input type="text" name="especialidade" x-model="user.especialidade" placeholder="Ex: Ortopedia, RPG, Pilates"
                                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:border-teal-500">
                            </div>
                        </div>
                    </div>
                </div>

                <template x-if="editMode">
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <input type="checkbox" id="is_active" name="is_active" value="1" :checked="user.is_active" class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="is_active" class="text-xs font-medium text-slate-700">Acesso ao sistema ativo</label>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
