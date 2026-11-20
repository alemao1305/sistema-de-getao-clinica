@extends('layouts.app')

@section('title', 'Extrato do Pacote - ' . $pacote->nome_pacote)
@section('header', 'Extrato de Consumo de Sessões')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ modalAjuste: false, tipoAjuste: 'bonus' }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $pacote->nome_pacote }}</h1>
            <p class="text-xs text-slate-500">Paciente: <span class="font-bold text-slate-700">{{ $pacote->paciente->nome }}</span> &bull; Validade: {{ $pacote->data_validade->format('d/m/Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('pacotes.manage'))
            <button type="button" @click="modalAjuste = true"
                    class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="sliders" class="w-4 h-4"></i>
                <span>Ajustar Saldo / Validade</span>
            </button>
            @endif
            <a href="{{ route('pacientes-pacotes.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Package Summary Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-3 bg-slate-50 rounded-xl">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Total de Sessões</span>
                <span class="text-2xl font-black text-slate-800">{{ $pacote->sessoes_totais }}</span>
            </div>
            <div class="p-3 bg-teal-50 rounded-xl">
                <span class="text-teal-600 block text-[10px] uppercase font-bold">Sessões Utilizadas</span>
                <span class="text-2xl font-black text-teal-800">{{ $pacote->sessoes_utilizadas }}</span>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl">
                <span class="text-emerald-600 block text-[10px] uppercase font-bold">Sessões Restantes</span>
                <span class="text-2xl font-black text-emerald-800">{{ $pacote->sessoes_restantes }}</span>
            </div>
            <div class="p-3 bg-purple-50 rounded-xl">
                <span class="text-purple-600 block text-[10px] uppercase font-bold">Situação</span>
                <span class="text-base font-bold text-purple-900 mt-1 block">{{ ucfirst($pacote->status) }}</span>
            </div>
        </div>
    </div>

    <!-- Consumption History Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Histórico Auditável de Baixas e Movimentações</h3>
            <span class="text-xs text-slate-400">{{ $pacote->consumos->count() }} registro(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Data / Hora</th>
                        <th class="py-3 px-4">Operação</th>
                        <th class="py-3 px-4 text-center">Qtd Sessões</th>
                        <th class="py-3 px-4">Motivo / Justificativa</th>
                        <th class="py-3 px-4">Responsável</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pacote->consumos as $c)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $c->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $opBadges = [
                                    'baixa_atendimento' => 'bg-teal-50 text-teal-800',
                                    'estorno' => 'bg-amber-50 text-amber-800',
                                    'ajuste_manual' => 'bg-blue-50 text-blue-800',
                                ];
                                $opLabels = [
                                    'baixa_atendimento' => 'Baixa de Atendimento',
                                    'estorno' => 'Estorno',
                                    'ajuste_manual' => 'Ajuste Manual / Crédito',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $opBadges[$c->tipo] ?? 'bg-slate-100' }}">
                                {{ $opLabels[$c->tipo] ?? $c->tipo }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold {{ $c->quantidade > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $c->quantidade > 0 ? '-' . $c->quantidade : ($c->quantidade < 0 ? '+' . abs($c->quantidade) : '0') }}
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $c->motivo }}
                            @if($c->agendamento_id)
                                <span class="text-slate-400 block text-[10px]">Agendamento #{{ $c->agendamento_id }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $c->responsavel->name ?? 'Sistema' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">Nenhum consumo registrado ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Ajustar Saldo / Validade -->
    <div x-show="modalAjuste" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200" @click.outside="modalAjuste = false">
            <h3 class="text-sm font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-teal-600"></i> Ajuste Administrativo de Saldo
            </h3>
            <p class="text-xs text-slate-500 mb-4">Todas as alterações geram registros de auditoria no extrato.</p>

            <form method="POST" action="{{ route('pacientes-pacotes.ajustar', $pacote->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ação a Executar *</label>
                    <select name="tipo_ajuste" x-model="tipoAjuste" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="bonus">Adicionar Sessão Bônus (Cortesia)</option>
                        <option value="adicionar">Acrescentar Sessões</option>
                        <option value="reduzir">Reduzir Saldo Manualmente</option>
                        <option value="alterar_validade">Prorrogar / Alterar Data de Validade</option>
                        <option value="pausar">Pausar Pacote Temporariamente</option>
                        <option value="ativar">Reativar Pacote</option>
                    </select>
                </div>

                <div x-show="['bonus', 'adicionar', 'reduzir'].includes(tipoAjuste)">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantidade de Sessões *</label>
                    <input type="number" name="quantidade" min="1" max="50" value="1"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div x-show="tipoAjuste === 'alterar_validade'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nova Data de Validade *</label>
                    <input type="date" name="nova_validade" value="{{ $pacote->data_validade->copy()->addDays(30)->format('Y-m-d') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Justificativa / Motivo do Ajuste *</label>
                    <textarea name="motivo" rows="2" required placeholder="Ex: Cortesia por indicação de amigo / Licença médica do paciente"
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalAjuste = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                        Confirmar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
