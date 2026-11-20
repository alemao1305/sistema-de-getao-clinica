@extends('layouts.app')

@section('title', 'Trilha de Auditoria')
@section('header', 'Trilha de Auditoria & Conformidade')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalDiffOpen: false,
    diffLog: null,
    verDetalhes(log) {
        this.diffLog = log;
        this.modalDiffOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('configuracoes.index') }}" class="hover:text-teal-600 transition">Configurações</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Auditoria</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Registro de Auditoria Imutável (LGPD)</h1>
            <p class="text-xs text-slate-500">Rastreabilidade completa de ações críticas, acessos clínicos, alterações financeiras e cadastrais.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Trilha Conforme Resolução COFFITO</span>
            </span>
        </div>
    </div>

    <!-- Filtros de Auditoria -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('auditoria.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-3">
                <label class="block font-semibold text-slate-700 mb-1">Buscar na Descrição</label>
                <input type="text" name="busca" value="{{ $busca }}" placeholder="Ex: paciente, evolução, venda..."
                       class="w-full text-xs rounded-xl border border-slate-300 p-2 focus:border-teal-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Ação / Evento</label>
                <input type="text" name="action" value="{{ $action }}" placeholder="Ex: prontuario, vendas"
                       class="w-full text-xs rounded-xl border border-slate-300 p-2 focus:border-teal-500">
            </div>

            <div class="sm:col-span-3">
                <label class="block font-semibold text-slate-700 mb-1">Usuário Responsável</label>
                <select name="user_id" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    <option value="">Todos os usuários</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }} ({{ $u->role_label }})</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="w-full text-xs rounded-xl border border-slate-300 p-2">
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Data Fim</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="data_fim" value="{{ $dataFim }}" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white p-2 rounded-xl transition shadow-sm" title="Filtrar">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                    @if($busca || $action || $userId || $dataInicio || $dataFim)
                    <a href="{{ route('auditoria.index') }}" class="p-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition" title="Limpar">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Logs -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Data / Hora</th>
                        <th class="py-3 px-4">Usuário</th>
                        <th class="py-3 px-4">Ação</th>
                        <th class="py-3 px-4">Descrição do Evento</th>
                        <th class="py-3 px-4">IP / Origem</th>
                        <th class="py-3 px-4 text-right">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3 px-4 font-sans text-xs">
                            @if($log->user)
                                <div class="font-bold text-slate-800">{{ $log->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->user->role_label }}</div>
                            @else
                                <span class="text-slate-400 italic">Sistema / Anônimo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[10px] font-bold
                                @if(str_contains($log->action, 'create')) bg-emerald-100 text-emerald-800
                                @elseif(str_contains($log->action, 'update')) bg-blue-100 text-blue-800
                                @elseif(str_contains($log->action, 'delete') || str_contains($log->action, 'cancel')) bg-rose-100 text-rose-800
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-sans text-xs text-slate-700 max-w-md">
                            {{ $log->description }}
                        </td>
                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if(!empty($log->new_values) || !empty($log->old_values))
                            <button @click="verDetalhes({{ json_encode($log) }})"
                                    class="p-1.5 rounded-lg text-teal-600 hover:bg-teal-50 transition" title="Ver Diffs e Metadados">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-sans">
                            Nenhum registro de auditoria encontrado para os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Diffs -->
    <div x-show="modalDiffOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalDiffOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Detalhes do Registro de Auditoria</h3>
                    <p class="text-xs text-slate-500" x-text="diffLog ? diffLog.action + ' • ' + diffLog.description : ''"></p>
                </div>
                <button @click="modalDiffOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <div class="space-y-4 text-xs font-sans" x-show="diffLog">
                <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-xl">
                    <div><span class="text-slate-500">Origem:</span> <span class="font-mono" x-text="diffLog?.ip_address"></span></div>
                    <div><span class="text-slate-500">Registro Afetado:</span> <span class="font-mono" x-text="diffLog?.auditable_type + ' #' + diffLog?.auditable_id"></span></div>
                </div>

                <div x-show="diffLog?.old_values" class="space-y-1">
                    <h4 class="font-bold text-rose-700 text-xs">Valores Anteriores:</h4>
                    <pre class="bg-rose-50/50 border border-rose-200 p-3 rounded-xl text-[11px] font-mono overflow-x-auto text-slate-800"
                         x-text="JSON.stringify(diffLog?.old_values, null, 2)"></pre>
                </div>

                <div x-show="diffLog?.new_values" class="space-y-1">
                    <h4 class="font-bold text-emerald-700 text-xs">Novos Valores Gravados:</h4>
                    <pre class="bg-emerald-50/50 border border-emerald-200 p-3 rounded-xl text-[11px] font-mono overflow-x-auto text-slate-800"
                         x-text="JSON.stringify(diffLog?.new_values, null, 2)"></pre>
                </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" @click="modalDiffOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
