<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Paciente;
use App\Models\ProntuarioAvaliacao;
use App\Models\ProntuarioEvolucao;
use App\Models\ProntuarioAditivo;
use App\Models\ModeloAvaliacao;
use App\Models\Agendamento;
use App\Models\ClinicaConfiguracao;
use App\Services\AuditService;

class ProntuarioController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->get('busca');

        $pacientes = Paciente::withCount(['avaliacoes', 'evolucoes'])
            ->when($busca, function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%");
            })
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('prontuarios.index', compact('pacientes', 'busca'));
    }

    public function show($pacienteId)
    {
        $paciente = Paciente::with([
            'avaliacoes' => fn($q) => $q->with(['profissional', 'modelo', 'aditivos.autor'])->orderBy('data_avaliacao', 'desc'),
            'evolucoes' => fn($q) => $q->with(['profissional', 'aditivos.autor'])->orderBy('data_hora_evolucao', 'desc'),
            'documentos' => fn($q) => $q->with('autor')->orderBy('created_at', 'desc'),
            'pacotes' => fn($q) => $q->with('servico'),
        ])->findOrFail($pacienteId);

        $modelos = ModeloAvaliacao::where('ativo', true)->get();

        AuditService::log('prontuario.view', "Acessou o prontuário clínico eletrônico do paciente {$paciente->nome}", $paciente);

        return view('prontuarios.show', compact('paciente', 'modelos'));
    }

    public function createAvaliacao($pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        $modelos = ModeloAvaliacao::where('ativo', true)->get();
        return view('prontuarios.create-avaliacao', compact('paciente', 'modelos'));
    }

    public function storeAvaliacao(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'agendamento_id' => ['nullable', 'exists:agendamentos,id'],
            'modelo_avaliacao_id' => ['nullable', 'exists:modelos_avaliacao,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'data_avaliacao' => ['required', 'date'],
            'queixa_principal' => ['required', 'string'],
            'historia_doenca_atual' => ['nullable', 'string'],
            'historia_patologica_pregressa' => ['nullable', 'string'],
            'medicacoes_em_uso' => ['nullable', 'string'],
            'escala_dor_eva' => ['required', 'integer', 'min:0', 'max:10'],
            'localizacao_dor' => ['nullable', 'string', 'max:255'],
            'caracteristica_dor' => ['nullable', 'string', 'max:255'],
            'fatores_melhora' => ['nullable', 'string'],
            'fatores_piora' => ['nullable', 'string'],
            'inspecao_palpacao' => ['nullable', 'string'],
            'amplitude_movimento' => ['nullable', 'string'],
            'forca_muscular' => ['nullable', 'string'],
            'testes_especiais_ortopedicos' => ['nullable', 'string'],
            'avaliacao_postural' => ['nullable', 'string'],
            'diagnostico_fisioterapeutico' => ['required', 'string'],
            'objetivos_terapeuticos' => ['required', 'string'],
            'plano_tratamento' => ['required', 'string'],
            'frequencia_semanal_sugerida' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $validated['profissional_id'] = $user->id;
        $validated['finalizado'] = true;
        $validated['finalizado_em'] = now();
        $validated['assinatura_profissional'] = "{$user->name} - {$user->crefito}";

        $avaliacao = ProntuarioAvaliacao::create($validated);

        AuditService::log('prontuario.avaliacao.create', "Cadastrou a avaliação '{$avaliacao->titulo}' para o paciente ID {$avaliacao->paciente_id}", $avaliacao);

        return redirect()->route('prontuarios.show', $avaliacao->paciente_id)
            ->with('success', 'Avaliação fisioterapêutica registrada e assinada com sucesso!');
    }

    public function storeEvolucao(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'agendamento_id' => ['nullable', 'exists:agendamentos,id'],
            'data_hora_evolucao' => ['nullable', 'date'],
            'escala_dor_inicial' => ['nullable', 'integer', 'min:0', 'max:10'],
            'relato_subjetivo' => ['required', 'string'],
            'procedimentos_realizados' => ['required', 'string'],
            'resposta_terapeutica' => ['nullable', 'string'],
            'escala_dor_final' => ['nullable', 'integer', 'min:0', 'max:10'],
            'orientacoes_domiciliares' => ['nullable', 'string'],
            'concluir_agendamento' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $paciente = Paciente::findOrFail($validated['paciente_id']);

        $numeroSessao = $paciente->evolucoes()->count() + 1;

        $evolucao = ProntuarioEvolucao::create([
            'paciente_id' => $paciente->id,
            'profissional_id' => $user->id,
            'agendamento_id' => $validated['agendamento_id'] ?? null,
            'data_hora_evolucao' => $validated['data_hora_evolucao'] ?? now(),
            'numero_sessao' => $numeroSessao,
            'escala_dor_inicial' => $validated['escala_dor_inicial'] ?? null,
            'relato_subjetivo' => $validated['relato_subjetivo'],
            'procedimentos_realizados' => $validated['procedimentos_realizados'],
            'resposta_terapeutica' => $validated['resposta_terapeutica'] ?? null,
            'escala_dor_final' => $validated['escala_dor_final'] ?? null,
            'orientacoes_domiciliares' => $validated['orientacoes_domiciliares'] ?? null,
            'finalizado' => true,
            'finalizado_em' => now(),
            'assinatura_profissional' => "{$user->name} - {$user->crefito}",
        ]);

        // Se veio do agendamento e solicitou conclusão
        if ($request->boolean('concluir_agendamento') && !empty($validated['agendamento_id'])) {
            $agendamento = Agendamento::find($validated['agendamento_id']);
            if ($agendamento && $agendamento->status !== 'concluido') {
                $agendamento->status = 'concluido';
                $agendamento->save();

                if ($agendamento->pacientePacote && $agendamento->pacientePacote->esta_valido) {
                    $agendamento->pacientePacote->consumirSessao($agendamento, "Baixa por evolução clínica registrada");
                }
            }
        }

        AuditService::log('prontuario.evolucao.create', "Registrou evolução da sessão #{$numeroSessao} para {$paciente->nome}", $evolucao);

        return back()->with('success', "Evolução da sessão #{$numeroSessao} registrada com sucesso!");
    }

    /**
     * Retificação ou aditivo de prontuário com justificativa obrigatória (LGPD e CFM/COFFITO)
     */
    public function storeAditivo(Request $request)
    {
        $validated = $request->validate([
            'registro_tipo' => ['required', 'in:avaliacao,evolucao'],
            'registro_id' => ['required', 'integer'],
            'justificativa' => ['required', 'string', 'min:5'],
            'conteudo_aditivo' => ['required', 'string', 'min:5'],
        ], [
            'justificativa.required' => 'A justificativa para a retificação é obrigatória por exigência ética e legal.',
            'conteudo_aditivo.required' => 'Descreva o conteúdo retificador ou aditivo.',
        ]);

        $aditivo = ProntuarioAditivo::create([
            'registro_tipo' => $validated['registro_tipo'],
            'registro_id' => $validated['registro_id'],
            'user_id' => Auth::id(),
            'justificativa' => $validated['justificativa'],
            'conteudo_aditivo' => $validated['conteudo_aditivo'],
        ]);

        AuditService::log('prontuario.aditivo.create', "Registrou retificação/aditivo no registro {$validated['registro_tipo']} #{$validated['registro_id']}: {$validated['justificativa']}", $aditivo);

        return back()->with('success', 'Aditivo retificador anexado ao prontuário com sucesso. O histórico anterior foi preservado.');
    }

    /**
     * Emissão de Atestado / Declaração Fisioterapêutica com timbrado
     */
    public function emitirDeclaracao(Request $request, $pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        $config = ClinicaConfiguracao::instance();
        $user = Auth::user();

        $tipo = $request->get('tipo', 'comparecimento'); // comparecimento, tratamento, alta
        $periodoTratamento = $request->get('periodo', 'nas datas acordadas');
        $observacoes = $request->get('observacoes', 'O paciente realiza tratamento fisioterapêutico para reabilitação cinético-funcional.');

        return view('prontuarios.declaracao-impressao', compact('paciente', 'config', 'user', 'tipo', 'periodoTratamento', 'observacoes'));
    }
}
