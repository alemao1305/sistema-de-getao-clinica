@extends('layouts.app')

@section('title', $paciente->nome)
@section('header', 'Dossiê do Paciente')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request()->get('tab', 'cadastral') }}' }">
    <!-- Patient Profile Header Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white font-bold text-xl flex items-center justify-center shadow-md shadow-teal-600/20">
                    {{ strtoupper(substr($paciente->nome, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $paciente->nome }}</h1>
                        @php
                            $statusBadges = [
                                'ativo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'inativo' => 'bg-slate-100 text-slate-600 border-slate-200',
                                'alta' => 'bg-teal-50 text-teal-700 border-teal-200',
                                'transferido' => 'bg-purple-50 text-purple-700 border-purple-200',
                            ];
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusBadges[$paciente->status] ?? 'bg-slate-100' }}">
                            {{ ucfirst($paciente->status) }}
                        </span>
                        @if($paciente->possui_convenio)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $paciente->convenio_nome }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                        <span>CPF: {{ $paciente->cpf_formatado ?? 'Não informado' }}</span>
                        <span>&bull;</span>
                        <span>{{ $paciente->idade ? "{$paciente->idade} anos" : 'Data de nascimento não informada' }}</span>
                        <span>&bull;</span>
                        <span>WhatsApp: {{ $paciente->telefone_formatado ?? '-' }}</span>
                        <span>&bull;</span>
                        <span>Desde: {{ $paciente->created_at->format('d/m/Y') }}</span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('agenda.index', ['paciente_id' => $paciente->id, 'novo' => 1]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    <span>Agendar</span>
                </a>
                <a href="{{ route('vendas.create', ['paciente_id' => $paciente->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Vender Pacote</span>
                </a>
                <a href="{{ route('pacientes.edit', $paciente->id) }}"
                   class="p-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition" title="Editar Dados Cadastrais">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 border-t border-slate-100 mt-6 pt-4 overflow-x-auto text-xs font-bold">
            <button @click="activeTab = 'cadastral'"
                    :class="activeTab === 'cadastral' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'text-slate-600 hover:text-slate-900 border-transparent hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl border transition flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Dados Cadastrais</span>
            </button>
            <button @click="activeTab = 'agenda'"
                    :class="activeTab === 'agenda' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'text-slate-600 hover:text-slate-900 border-transparent hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl border transition flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span>Agendamentos ({{ $paciente->agendamentos->count() }})</span>
            </button>
            <button @click="activeTab = 'prontuario'"
                    :class="activeTab === 'prontuario' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'text-slate-600 hover:text-slate-900 border-transparent hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl border transition flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-4 h-4"></i>
                <span>Prontuário & Evoluções ({{ $paciente->evolucoes->count() }})</span>
            </button>
            <button @click="activeTab = 'pacotes'"
                    :class="activeTab === 'pacotes' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'text-slate-600 hover:text-slate-900 border-transparent hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl border transition flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Pacotes & Sessões ({{ $paciente->pacotes->count() }})</span>
            </button>
            <button @click="activeTab = 'documentos'"
                    :class="activeTab === 'documentos' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'text-slate-600 hover:text-slate-900 border-transparent hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl border transition flex items-center gap-2">
                <i data-lucide="paperclip" class="w-4 h-4"></i>
                <span>Documentos & Anexos ({{ $paciente->documentos->count() }})</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: Dados Cadastrais -->
    <div x-show="activeTab === 'cadastral'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Dados Pessoais & Contatos -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-teal-600"></i> Informações Pessoais
                </h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                    <div>
                        <dt class="text-slate-500">Nome Completo</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->nome }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">CPF</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->cpf_formatado ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">RG</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->rg ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Data de Nascimento</dt>
                        <dd class="font-bold text-slate-800">
                            {{ $paciente->data_nascimento ? $paciente->data_nascimento->format('d/m/Y') : '-' }}
                            @if($paciente->idade) ({{ $paciente->idade }} anos) @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Sexo Biológico</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->sexo ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Profissão</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->profissao ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">WhatsApp</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->telefone_formatado ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">E-mail</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->email ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Endereço & Convênio -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-teal-600"></i> Endereço & Convênio
                </h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                    <div class="col-span-2">
                        <dt class="text-slate-500">Endereço Completo</dt>
                        <dd class="font-bold text-slate-800">
                            {{ $paciente->logradouro ? "{$paciente->logradouro}, {$paciente->numero}" : 'Não informado' }}
                            {{ $paciente->complemento ? " - {$paciente->complemento}" : '' }}
                            {{ $paciente->bairro ? " - {$paciente->bairro}" : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Cidade / Estado</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->cidade ? "{$paciente->cidade}/{$paciente->estado}" : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">CEP</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->cep ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Possui Convênio?</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->possui_convenio ? 'Sim' : 'Particular' }}</dd>
                    </div>
                    @if($paciente->possui_convenio)
                    <div>
                        <dt class="text-slate-500">Operadora</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->convenio_nome }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Nº Carteirinha</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->convenio_carteirinha ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Validade</dt>
                        <dd class="font-bold text-slate-800">{{ $paciente->convenio_validade ? $paciente->convenio_validade->format('d/m/Y') : '-' }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Termo de Consentimento LGPD -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Conformidade LGPD & Termo de Consentimento
            </h3>
            <div class="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
                <div>
                    <span class="font-bold block">Consentimento para Tratamento de Dados Registrado</span>
                    <span class="text-emerald-700 text-[11px]">
                        Autorizado em: {{ $paciente->consentimento_lgpd_em ? $paciente->consentimento_lgpd_em->format('d/m/Y H:i') : $paciente->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-200 text-emerald-800 font-bold text-[10px]">Ativo</span>
            </div>
        </div>
    </div>

    <!-- TAB 2: Agendamentos -->
    <div x-show="activeTab === 'agenda'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" style="display: none;">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Histórico de Agendamentos</h3>
            <a href="{{ route('agenda.index', ['paciente_id' => $paciente->id, 'novo' => 1]) }}"
               class="text-xs font-semibold text-teal-600 hover:underline flex items-center gap-1">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agendar novo
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Data & Horário</th>
                        <th class="py-3 px-4">Serviço</th>
                        <th class="py-3 px-4">Fisioterapeuta</th>
                        <th class="py-3 px-4">Sala</th>
                        <th class="py-3 px-4">Cobrança</th>
                        <th class="py-3 px-4">Situação</th>
                        <th class="py-3 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($paciente->agendamentos as $ag)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $ag->data_hora_inicio->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4">{{ $ag->servico->nome ?? '-' }}</td>
                        <td class="py-3 px-4 font-medium">{{ $ag->profissional->name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $ag->sala->nome ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <span class="capitalize text-slate-600">{{ $ag->tipo_cobranca }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php $b = $ag->status_badge; @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $b['bg'] }} {{ $b['text'] }}">
                                {{ $b['label'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('agenda.atendimento', $ag->id) }}" class="text-teal-600 hover:underline font-semibold">
                                Ver Detalhes
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Nenhum agendamento registrado para este paciente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: Prontuário & Evoluções -->
    <div x-show="activeTab === 'prontuario'" class="space-y-6" style="display: none;">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Registro Clínico Eletrônico</h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('prontuarios.show', $paciente->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Abrir Prontuário Completo</span>
                </a>
            </div>
        </div>

        <!-- Timeline de Evoluções Recentes -->
        <div class="space-y-4">
            @forelse($paciente->evolucoes as $evolucao)
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 font-bold text-xs">
                            Sessão #{{ $evolucao->numero_sessao ?? '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-800">
                            {{ $evolucao->data_hora_evolucao->format('d/m/Y H:i') }}
                        </span>
                        <span class="text-xs text-slate-400">&bull; Fisioterapeuta: {{ $evolucao->profissional->name }}</span>
                    </div>
                    @if($evolucao->escala_dor_inicial !== null)
                    <div class="flex items-center gap-1 text-xs">
                        <span class="text-slate-500">Dor EVA:</span>
                        <span class="font-bold text-rose-600">{{ $evolucao->escala_dor_inicial }}/10</span>
                        @if($evolucao->escala_dor_final !== null)
                            <span class="text-slate-400">&rarr;</span>
                            <span class="font-bold text-emerald-600">{{ $evolucao->escala_dor_final }}/10</span>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="text-xs space-y-2">
                    <div>
                        <span class="font-bold text-slate-700">Relato Subjetivo:</span>
                        <p class="text-slate-600 mt-0.5">{{ $evolucao->relato_subjetivo }}</p>
                    </div>
                    <div>
                        <span class="font-bold text-slate-700">Procedimentos & Conduta:</span>
                        <p class="text-slate-600 mt-0.5">{{ $evolucao->procedimentos_realizados }}</p>
                    </div>
                    @if($evolucao->orientacoes_domiciliares)
                    <div>
                        <span class="font-bold text-slate-700">Orientações:</span>
                        <p class="text-slate-600 mt-0.5">{{ $evolucao->orientacoes_domiciliares }}</p>
                    </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Assinado eletronicamente por: {{ $evolucao->assinatura_profissional ?? $evolucao->profissional->name }}</span>
                    <span>Registro imutável conforme LGPD</span>
                </div>
            </div>
            @empty
            <div class="bg-white p-8 rounded-2xl text-center text-slate-400 border border-slate-200">
                <i data-lucide="clipboard-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <p class="text-sm font-medium">Nenhuma evolução de sessão registrada ainda.</p>
                <a href="{{ route('prontuarios.show', $paciente->id) }}" class="mt-2 inline-block text-xs text-teal-600 font-semibold hover:underline">
                    Realizar Avaliação Inicial
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: Pacotes & Saldos -->
    <div x-show="activeTab === 'pacotes'" class="space-y-4" style="display: none;">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Pacotes de Sessões Contratados</h3>
            <a href="{{ route('vendas.create', ['paciente_id' => $paciente->id]) }}"
               class="text-xs font-semibold text-teal-600 hover:underline flex items-center gap-1">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Vender novo pacote
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($paciente->pacotes as $pacote)
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">{{ $pacote->nome_pacote }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pacote->status === 'ativo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($pacote->status) }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    @php
                        $percent = $pacote->sessoes_totais > 0 ? round(($pacote->sessoes_utilizadas / $pacote->sessoes_totais) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                            <span class="text-slate-600">Consumo de Sessões</span>
                            <span class="text-teal-700">{{ $pacote->sessoes_utilizadas }} de {{ $pacote->sessoes_totais }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-teal-600 h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center mt-4 pt-3 border-t border-slate-100 text-xs">
                        <div class="bg-slate-50 p-2 rounded-xl">
                            <span class="text-slate-400 block text-[10px] uppercase">Totais</span>
                            <span class="font-bold text-slate-800">{{ $pacote->sessoes_totais }}</span>
                        </div>
                        <div class="bg-teal-50 p-2 rounded-xl">
                            <span class="text-teal-600 block text-[10px] uppercase">Realizadas</span>
                            <span class="font-bold text-teal-800">{{ $pacote->sessoes_utilizadas }}</span>
                        </div>
                        <div class="bg-emerald-50 p-2 rounded-xl">
                            <span class="text-emerald-600 block text-[10px] uppercase">Restantes</span>
                            <span class="font-bold text-emerald-800">{{ $pacote->sessoes_restantes }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Validade: {{ $pacote->data_validade->format('d/m/Y') }}</span>
                    <a href="{{ route('pacientes-pacotes.show', $pacote->id) }}" class="font-semibold text-teal-600 hover:underline">
                        Ver Extrato Completo &rarr;
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-2 bg-white p-8 rounded-2xl text-center text-slate-400 border border-slate-200">
                <i data-lucide="layers" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <p class="text-sm font-medium">Nenhum pacote contratado no momento.</p>
                <a href="{{ route('vendas.create', ['paciente_id' => $paciente->id]) }}" class="mt-2 inline-block text-xs text-teal-600 font-semibold hover:underline">
                    Registrar Venda de Pacote
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 5: Documentos & Anexos -->
    <div x-show="activeTab === 'documentos'" class="space-y-4" style="display: none;">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Documentos e Exames Anexados</h3>
            <button type="button" @click="$refs.uploadModal.showModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                <span>Anexar Documento</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 divide-y divide-slate-100">
            @forelse($paciente->documentos as $doc)
            <div class="p-4 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-slate-100 text-slate-600">
                        <i data-lucide="file" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <span class="font-bold text-slate-900 block text-sm">{{ $doc->titulo }}</span>
                        <span class="text-slate-500">
                            {{ $doc->tipo_formatado }} &bull; Anexado em {{ $doc->created_at->format('d/m/Y H:i') }} por {{ $doc->autor->name }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('documentos.download', $doc->id) }}"
                       class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold flex items-center gap-1 transition">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Baixar
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400">
                <i data-lucide="paperclip" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <p class="text-sm font-medium">Nenhum documento ou exame anexado a este paciente.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Upload Document Modal -->
    <dialog x-ref="uploadModal" class="rounded-2xl p-6 shadow-2xl backdrop:bg-slate-900/60 max-w-md w-full border border-slate-200">
        <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">

            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-sm font-bold text-slate-900">Anexar Novo Arquivo</h3>
                <button type="button" @click="$refs.uploadModal.close()" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Documento *</label>
                <input type="text" name="titulo" required placeholder="Ex: Ressonância Magnética Joelho Direito"
                       class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Arquivo</label>
                <select name="tipo_documento" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    <option value="exame_imagem">Exame de Imagem (RX / RM / USG)</option>
                    <option value="laudo">Laudo Médico / Pericial</option>
                    <option value="atestado">Atestado / Declaração</option>
                    <option value="consentimento_lgpd">Termo LGPD Assinado</option>
                    <option value="encaminhamento">Encaminhamento Médico</option>
                    <option value="outro">Outro Arquivo</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Selecionar Arquivo * (PDF, JPG, PNG)</label>
                <input type="file" name="arquivo" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Observações</label>
                <textarea name="observacoes" rows="2" class="w-full text-xs rounded-xl border border-slate-300 p-2"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="$refs.uploadModal.close()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold">
                    Enviar Arquivo
                </button>
            </div>
        </form>
    </dialog>
</div>
@endsection
