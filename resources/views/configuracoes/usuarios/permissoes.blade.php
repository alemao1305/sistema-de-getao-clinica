@extends('layouts.app')

@section('title', 'Matriz de Permissões')
@section('header', 'Matriz de Permissões por Perfil')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    abaAtiva: '{{ $roles->first()->id ?? 1 }}'
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <a href="{{ route('usuarios.index') }}" class="hover:text-teal-600 transition">Usuários</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Permissões</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Matriz de Controle de Acesso (RBAC)</h1>
            <p class="text-xs text-slate-500">Configure as permissões granulares de visualização, edição e exclusão atribuídas a cada perfil de usuário.</p>
        </div>

        <a href="{{ route('usuarios.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Voltar para Usuários</span>
        </a>
    </div>

    <!-- Navegação por Perfis -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto">
        @foreach($roles as $role)
        <button @click="abaAtiva = '{{ $role->id }}'"
                :class="abaAtiva == '{{ $role->id }}' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 font-medium'"
                class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition">
            {{ $role->description ?? $role->name }}
            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px]"
                  :class="abaAtiva == '{{ $role->id }}' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                {{ $role->permissions->count() }}
            </span>
        </button>
        @endforeach
    </div>

    <!-- Formulários por Perfil -->
    @foreach($roles as $role)
    <div x-show="abaAtiva == '{{ $role->id }}'" class="space-y-6">
        <form method="POST" action="{{ route('usuarios.salvar-permissoes', $role->id) }}">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="shield" class="w-5 h-5 text-teal-600"></i>
                            Permissões para: <span class="text-teal-700">{{ $role->description ?? $role->name }}</span>
                        </h2>
                        <p class="text-xs text-slate-500">Marque as permissões que os usuários deste perfil terão no sistema.</p>
                    </div>

                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition inline-flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Salvar Permissões</span>
                    </button>
                </div>

                <!-- Módulos de Permissões -->
                <div class="space-y-6">
                    @php
                        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                    @endphp

                    @foreach($permissionsByModule as $modulo => $permissoes)
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Módulo: {{ ucfirst($modulo) }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($permissoes as $perm)
                            <label class="flex items-start gap-2.5 p-2.5 rounded-lg bg-white border border-slate-200 hover:border-teal-300 transition cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                       @checked(in_array($perm->id, $rolePermissionIds))
                                       class="rounded text-teal-600 focus:ring-teal-500 mt-0.5">
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-slate-800">{{ $perm->description ?? $perm->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow-sm transition inline-flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Salvar Permissões do Perfil</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endsection
