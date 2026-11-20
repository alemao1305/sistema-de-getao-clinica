@extends('layouts.app')

@section('title', 'Convênios & Guias')
@section('header', 'Gestão de Convênios & Guias de Autorização')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    aba: '{{ $aba }}',
    modalConvenioOpen: false,
    modalGuiaOpen: false,
    modalAtualizarGuiaOpen: false,
    guiaSelecionada: null,
    statusGuia: 'autorizada',
    sessoesExecutadas: 0,
    valorGlosado: 0,
    motivoGlosa: '',
    abrirAtualizarGuia(g) {
        this.guiaSelecionada = g;
        this.statusGuia = g.status;
        this.sessoesExecutadas = g.sessoes_executadas;
        this.valorGlosado = g.valor_glosado || 0;
        this.motivoGlosa = g.motivo_glosa || '';
        this.modalAtualizarGuiaOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Operadoras de Saúde & Guias</h1>
            <p class="text-xs text-slate-500">Controle o credenciamento de planos, guias de autorização TISS, execução de sessões e prevenção de glosas.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="modalConvenioOpen = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="plus" class="w-4 h-4 text-teal-600"></i>
                <span>Nova Operadora</span>
            </button>

            <button @click="modalGuiaOpen = true"
                    class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                <span>Nova Guia de Autorização</span>
            </button>
        </div>
    </div>

    <!-- Navegação de Abas -->
    <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
        <button @click="aba = 'guias'"
                :class="aba === 'guias' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 font-medium'"
                class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            <span>Guias & Autorizações</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                  :class="aba === 'guias' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                {{ $guias->total() }}
            </span>
        </button>

        <button @click="aba = 'operadoras'"
                :class="aba === 'operadoras' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 font-medium'"
                class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
            <i data-lucide="shield" class="w-4 h-4"></i>
            <span>Operadoras Cadastradas</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                  :class="aba === 'operadoras' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                {{ $convenios->count() }}
            </span>
        </button>
    </div>

    <!-- Conteúdo da Aba 1: Guias de Autorização -->
    <div x-show="aba === 'guias'" class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Nº Guia / Convênio</th>
                            <th class="py-3 px-4">Paciente</th>
                            <th class="py-3 px-4">Procedimento</th>
                            <th class="py-3 px-4">Sessões (Exec / Aut)</th>
                            <th class="py-3 px-4">Validade</th>
                            <th class="py-3 px-4">Valor Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($guias as $guia)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-sm font-mono">{{ $guia->numero_guia }}</div>
                                <div class="text-[11px] text-teal-700 font-semibold">{{ $guia->convenio->nome ?? '—' }}</div>
                                <div class="text-[10px] text-slate-400">Cartão: {{ $guia->numero_carteirinha }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $guia->paciente->nome ?? '—' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $guia->paciente->telefone ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $guia->servico->nome ?? 'Sessão Fisioterapia' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-xs">
                                    {{ $guia->sessoes_executadas }} / {{ $guia->sessoes_autorizadas }}
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                    @php
                                        $perc = $guia->sessoes_autorizadas > 0 ? min(100, round(($guia->sessoes_executadas / $guia->sessoes_autorizadas) * 100)) : 0;
                                    @endphp
                                    <div class="h-1.5 rounded-full {{ $perc >= 100 ? 'bg-emerald-500' : 'bg-teal-500' }}" style="width: {{ $perc }}%;"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $guia->validade_guia ? \Carbon\Carbon::parse($guia->validade_guia)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                R$ {{ number_format($guia->valor_total_guia, 2, ',', '.') }}
                                @if($guia->valor_glosado > 0)
                                    <div class="text-[10px] text-rose-600 font-semibold">Glosa: R$ {{ number_format($guia->valor_glosado, 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($guia->status === 'autorizada')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">Autorizada</span>
                                @elseif($guia->status === 'em_execucao')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Em Execução</span>
                                @elseif($guia->status === 'concluida')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-800">Concluída</span>
                                @elseif($guia->status === 'faturada')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800">Faturada</span>
                                @elseif($guia->status === 'paga')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Paga</span>
                                @elseif($guia->status === 'glosada')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">Glosada</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button @click="abrirAtualizarGuia({{ json_encode($guia) }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition" title="Atualizar Guia / Registrar Glosa">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 text-xs">
                                Nenhuma guia de convênio cadastrada até o momento.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($guias->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $guias->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Conteúdo da Aba 2: Operadoras Cadastradas -->
    <div x-show="aba === 'operadoras'" class="space-y-4" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($convenios as $conv)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-bold text-slate-900 text-sm">{{ $conv->nome }}</div>
                        <div class="text-[11px] text-slate-500">Registro ANS: {{ $conv->registro_ans ?? 'Não informado' }}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-800">
                        {{ $conv->guias_count }} guias
                    </span>
                </div>

                <div class="text-xs text-slate-600 space-y-1 pt-2 border-t border-slate-100">
                    <div><span class="text-slate-400">CNPJ:</span> {{ $conv->cnpj ?? 'Não informado' }}</div>
                    <div><span class="text-slate-400">Telefone:</span> {{ $conv->telefone ?? 'Não informado' }}</div>
                    <div><span class="text-slate-400">Prazo de Pagamento:</span> {{ $conv->prazo_pagamento_dias }} dias</div>
                </div>

                @if($conv->observacoes)
                <p class="text-[11px] text-slate-500 italic bg-slate-50 p-2.5 rounded-xl">
                    {{ $conv->observacoes }}
                </p>
                @endif
            </div>
            @empty
            <div class="col-span-3 py-12 text-center text-slate-500 text-xs">
                Nenhuma operadora de convênio cadastrada.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Nova Operadora -->
    <div x-show="modalConvenioOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalConvenioOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Cadastrar Operadora de Convênio</h3>
                <button @click="modalConvenioOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form action="{{ route('convenios.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome da Operadora / Plano *</label>
                    <input type="text" name="nome" required placeholder="Ex: Unimed, Bradesco Saúde, SulAmérica"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Registro ANS</label>
                        <input type="text" name="registro_ans" placeholder="Ex: 005711"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">CNPJ</label>
                        <input type="text" name="cnpj" placeholder="00.000.000/0001-00"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone da Central</label>
                        <input type="text" name="telefone" placeholder="0800..."
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prazo Pagamento (dias) *</label>
                        <input type="number" name="prazo_pagamento_dias" value="30" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail de Faturamento</label>
                    <input type="email" name="email" placeholder="faturamento@operadora.com.br"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observações de Glosas / TISS</label>
                    <textarea name="observacoes" rows="2" placeholder="Regras de envio de guias e prazos recursais..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalConvenioOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Operadora
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Nova Guia -->
    <div x-show="modalGuiaOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalGuiaOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Cadastrar Guia de Autorização TISS</h3>
                <button @click="modalGuiaOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form action="{{ route('convenios.guias.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Operadora / Convênio *</label>
                        <select name="convenio_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            @foreach($convenios as $c)
                                <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Paciente *</label>
                        <select name="paciente_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            @foreach($pacientes as $pac)
                                <option value="{{ $pac->id }}">{{ $pac->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Número da Guia *</label>
                        <input type="text" name="numero_guia" required placeholder="Ex: 2026110987"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Carteirinha do Paciente *</label>
                        <input type="text" name="numero_carteirinha" required placeholder="Ex: 001234567890"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Procedimento</label>
                        <select name="servico_id" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">Fisioterapia Geral (Padrão)</option>
                            @foreach($servicos as $s)
                                <option value="{{ $s->id }}">{{ $s->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Validade da Guia *</label>
                        <input type="date" name="validade_guia" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sessões Autorizadas *</label>
                        <input type="number" name="sessoes_autorizadas" value="10" min="1" max="100" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Unitário da Sessão (R$) *</label>
                        <input type="number" step="0.01" name="valor_sessao_acordado" value="75.00" min="0" required
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Observações / CID / Senha de Autorização</label>
                    <textarea name="observacoes" rows="2" placeholder="Senha do portal, indicação médica, CID..."
                              class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalGuiaOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Salvar Guia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Atualizar Guia / Registrar Glosa -->
    <div x-show="modalAtualizarGuiaOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="modalAtualizarGuiaOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Atualizar Andamento da Guia</h3>
                    <p class="text-xs text-slate-500" x-text="guiaSelecionada ? 'Guia nº ' + guiaSelecionada.numero_guia : ''"></p>
                </div>
                <button @click="modalAtualizarGuiaOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="'/convenios/guias/' + guiaSelecionada?.id" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status da Guia *</label>
                        <select name="status" x-model="statusGuia" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="autorizada">Autorizada</option>
                            <option value="em_execucao">Em Execução</option>
                            <option value="concluida">Concluída</option>
                            <option value="faturada">Faturada</option>
                            <option value="paga">Paga pela Operadora</option>
                            <option value="glosada">Glosada (Recusada)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sessões Realizadas</label>
                        <input type="number" name="sessoes_executadas" x-model="sessoesExecutadas" min="0"
                               class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                </div>

                <div x-show="statusGuia === 'glosada'" class="space-y-3 p-3 bg-rose-50 rounded-xl border border-rose-200">
                    <h4 class="font-bold text-rose-800 text-xs">Informações sobre a Glosa:</h4>
                    <div>
                        <label class="block text-xs font-semibold text-rose-700 mb-1">Valor Glosado (R$)</label>
                        <input type="number" step="0.01" name="valor_glosado" x-model="valorGlosado" min="0"
                               class="w-full text-xs rounded-xl border border-rose-300 p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-rose-700 mb-1">Motivo / Justificativa da Glosa</label>
                        <textarea name="motivo_glosa" x-model="motivoGlosa" rows="2" placeholder="Ex: Falta de assinatura na folha TISS, data expirada..."
                                  class="w-full text-xs rounded-xl border border-rose-300 p-2"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalAtualizarGuiaOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                        Atualizar Guia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
