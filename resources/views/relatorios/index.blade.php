@extends('layouts.app')

@section('title', 'Relatórios Dinâmicos')
@section('header', 'Gerador de Relatórios & Inteligência Clínica')

@section('content')
<div class="space-y-6" x-data="{ modalSalvar: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Relatórios Configuráveis</h1>
            <p class="text-xs text-slate-500">Gere relatórios operacionais, clínicos e financeiros com filtros e colunas sob medida.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Botão Salvar Modelo -->
            <button type="button" @click="modalSalvar = true"
                    class="px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i data-lucide="bookmark-plus" class="w-4 h-4 text-teal-600"></i>
                <span>Salvar Modelo</span>
            </button>

            <!-- Exportar CSV -->
            <a href="{{ route('relatorios.csv', request()->query()) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                <span>Exportar CSV</span>
            </a>

            <!-- Imprimir / PDF -->
            <a href="{{ route('relatorios.pdf', request()->query()) }}" target="_blank"
               class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Imprimir / PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter & Column Builder Box -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <form method="GET" action="{{ route('relatorios.index') }}" class="space-y-4">
            <!-- 1. Fonte de Dados & Período -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fonte de Dados *</label>
                    <select name="fonte" onchange="this.form.submit()" class="w-full text-xs font-semibold rounded-xl border border-slate-300 p-2.5 focus:ring-teal-600">
                        <option value="agendamentos" {{ $fonteDados === 'agendamentos' ? 'selected' : '' }}>Agendamentos & Ocupação de Agenda</option>
                        <option value="vendas" {{ $fonteDados === 'vendas' ? 'selected' : '' }}>Vendas de Serviços & Pacotes</option>
                        <option value="sessoes" {{ $fonteDados === 'sessoes' ? 'selected' : '' }}>Saldos & Consumo de Sessões por Paciente</option>
                        <option value="financeiro_receitas" {{ $fonteDados === 'financeiro_receitas' ? 'selected' : '' }}>Financeiro - Contas a Receber / Receitas</option>
                        <option value="financeiro_despesas" {{ $fonteDados === 'financeiro_despesas' ? 'selected' : '' }}>Financeiro - Contas a Pagar / Despesas</option>
                        <option value="repasses" {{ $fonteDados === 'repasses' ? 'selected' : '' }}>Desempenho & Repasses a Profissionais</option>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Data Início</label>
                    <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" value="{{ $dataFim }}" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
            </div>

            <!-- 2. Filtros Específicos -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1 border-t border-slate-100">
                @if(in_array($fonteDados, ['agendamentos', 'repasses']))
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Fisioterapeuta</label>
                    <select name="profissional_id" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                        <option value="">Todos os profissionais</option>
                        @foreach($profissionais as $prof)
                            <option value="{{ $prof->id }}" {{ (string)$profissionalId === (string)$prof->id ? 'selected' : '' }}>{{ $prof->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($fonteDados === 'agendamentos')
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sala</label>
                    <select name="sala_id" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                        <option value="">Todas as salas</option>
                        @foreach($salas as $s)
                            <option value="{{ $s->id }}" {{ (string)$salaId === (string)$s->id ? 'selected' : '' }}>{{ $s->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(in_array($fonteDados, ['agendamentos', 'vendas', 'sessoes', 'financeiro_receitas']))
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Paciente</label>
                    <select name="paciente_id" class="w-full text-xs rounded-xl border border-slate-300 p-2">
                        <option value="">Todos os pacientes</option>
                        @foreach($pacientes as $pac)
                            <option value="{{ $pac->id }}" {{ (string)$pacienteId === (string)$pac->id ? 'selected' : '' }}>{{ $pac->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Situação / Status</label>
                    <input type="text" name="status" value="{{ $status }}" placeholder="Ex: concluido, pago, pendente..."
                           class="w-full text-xs rounded-xl border border-slate-300 p-2">
                </div>
            </div>

            <!-- 3. Seletor de Colunas Visíveis -->
            <div class="pt-2 border-t border-slate-100">
                <span class="block text-xs font-bold text-slate-700 mb-2">Colunas para Exibir no Relatório:</span>
                <div class="flex flex-wrap gap-3">
                    @foreach($colunasDisponiveis as $colKey => $colMeta)
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-100">
                        <input type="checkbox" name="colunas[]" value="{{ $colKey }}"
                               {{ in_array($colKey, $colunasSelecionadas) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        <span>{{ $colMeta['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">Total de registros encontrados: {{ $resultado['totais']['quantidade'] }}</span>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs py-2.5 px-6 rounded-xl shadow-sm transition">
                    Atualizar Relatório
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Prévia do Relatório ({{ $resultado['totais']['quantidade'] }} itens)
            </h3>
            @if(isset($resultado['totais']['valor']) && $resultado['totais']['valor'] > 0)
                <span class="text-xs font-bold text-teal-800 bg-teal-100/60 px-3 py-1 rounded-full">
                    Totalizador Financeiro: R$ {{ number_format($resultado['totais']['valor'], 2, ',', '.') }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 font-bold text-slate-600 uppercase">
                        @foreach($colunasSelecionadas as $cKey)
                            <th class="py-3 px-4">{{ $colunasDisponiveis[$cKey]['label'] ?? $cKey }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($resultado['dados'] as $linha)
                    <tr class="hover:bg-slate-50 transition">
                        @foreach($colunasSelecionadas as $cKey)
                            <td class="py-3 px-4 text-slate-800">
                                {{ $linha[$cKey] ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($colunasSelecionadas) ?: 1 }}" class="py-12 text-center text-slate-400 font-medium">
                            Nenhum registro encontrado para os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Salvar Modelo Favorito -->
    <div x-show="modalSalvar" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200" @click.outside="modalSalvar = false">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="bookmark-plus" class="w-4 h-4 text-teal-600"></i> Salvar Modelo de Relatório
            </h3>

            <form method="POST" action="{{ route('relatorios.salvar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="fonte_dados" value="{{ $fonteDados }}">
                <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                <input type="hidden" name="profissional_id" value="{{ $profissionalId }}">
                <input type="hidden" name="sala_id" value="{{ $salaId }}">
                <input type="hidden" name="paciente_id" value="{{ $pacienteId }}">
                <input type="hidden" name="status" value="{{ $status }}">
                @foreach($colunasSelecionadas as $cKey)
                    <input type="hidden" name="colunas_selecionadas[]" value="{{ $cKey }}">
                @endforeach

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome do Modelo *</label>
                    <input type="text" name="nome" required placeholder="Ex: Ocupação Semanal Ortopedia"
                           class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="favorito" name="favorito" value="1" checked class="rounded border-slate-300 text-teal-600">
                    <label for="favorito" class="text-xs text-slate-700">Salvar como favorito no painel</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="modalSalvar = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                        Salvar Modelo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
