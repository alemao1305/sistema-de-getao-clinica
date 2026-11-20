<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Sala;
use App\Models\Servico;
use App\Models\PacientePacote;
use App\Models\RepasseProfissional;
use App\Models\ClinicaConfiguracao;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->get('modo', 'dia'); // dia, semana, mes, lista
        $dataRef = $request->get('data') ? Carbon::parse($request->get('data')) : Carbon::today();
        
        $profissionalId = $request->get('profissional_id');
        $salaId = $request->get('sala_id');
        $status = $request->get('status');

        $user = Auth::user();
        if ($user->isFisioterapeuta() && !$profissionalId) {
            $profissionalId = $user->id; // Fisioterapeuta visualiza sua agenda por padrão
        }

        // Calcula o intervalo de datas conforme o modo de visualização
        [$dataInicio, $dataFim] = match ($viewMode) {
            'semana' => [$dataRef->copy()->startOfWeek(), $dataRef->copy()->endOfWeek()->endOfDay()],
            'mes' => [$dataRef->copy()->startOfMonth(), $dataRef->copy()->endOfMonth()->endOfDay()],
            default => [$dataRef->copy()->startOfDay(), $dataRef->copy()->endOfDay()],
        };

        $query = Agendamento::with(['paciente', 'profissional', 'sala', 'servico', 'pacientePacote'])
            ->whereBetween('data_hora_inicio', [$dataInicio, $dataFim])
            ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
            ->when($salaId, fn($q) => $q->where('sala_id', $salaId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('data_hora_inicio', 'asc');

        $agendamentos = $query->get();

        // Bloqueios de agenda no período
        $bloqueios = BloqueioAgenda::with(['profissional', 'sala'])
            ->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_hora_inicio', [$dataInicio, $dataFim])
                  ->orWhereBetween('data_hora_fim', [$dataInicio, $dataFim]);
            })
            ->when($profissionalId, fn($q) => $q->where(fn($sq) => $sq->whereNull('profissional_id')->orWhere('profissional_id', $profissionalId)))
            ->when($salaId, fn($q) => $q->where(fn($sq) => $sq->whereNull('sala_id')->orWhere('sala_id', $salaId)))
            ->get();

        // Dados auxiliares para filtros e modais
        $profissionais = User::whereIn('role', ['fisioterapeuta', 'proprietaria'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $salas = Sala::where('ativa', true)->orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        $pacientes = Paciente::where('status', 'ativo')->orderBy('nome')->get();

        return view('agenda.index', compact(
            'agendamentos',
            'bloqueios',
            'viewMode',
            'dataRef',
            'profissionalId',
            'salaId',
            'status',
            'profissionais',
            'salas',
            'servicos',
            'pacientes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'profissional_id' => ['required', 'exists:users,id'],
            'sala_id' => ['nullable', 'exists:salas,id'],
            'servico_id' => ['required', 'exists:servicos,id'],
            'data' => ['required', 'date'],
            'horario' => ['required', 'date_format:H:i'],
            'duracao_minutos' => ['required', 'integer', 'min:15', 'max:240'],
            'tipo_cobranca' => ['required', 'in:avulso,pacote,convenio,retorno_gratuito'],
            'observacoes' => ['nullable', 'string'],
            'recorrencia' => ['nullable', 'in:nenhuma,semanal,quinzenal'],
            'quantidade_recorrencias' => ['nullable', 'integer', 'min:1', 'max:20'],
        ], [
            'paciente_id.required' => 'Selecione o paciente.',
            'profissional_id.required' => 'Selecione o fisioterapeuta responsável.',
            'servico_id.required' => 'Selecione o serviço a ser realizado.',
            'data.required' => 'Informe a data do agendamento.',
            'horario.required' => 'Informe o horário de início.',
        ]);

        $dataHoraInicio = Carbon::parse("{$validated['data']} {$validated['horario']}");
        $duracao = (int) $validated['duracao_minutos'];
        $dataHoraFim = $dataHoraInicio->copy()->addMinutes($duracao);

        // 1. Verificação estrita de conflito de horário
        $conflito = Agendamento::checkConflict(
            $validated['profissional_id'],
            $validated['sala_id'] ?? null,
            $dataHoraInicio,
            $dataHoraFim
        );

        if ($conflito) {
            return back()->withInput()->with('error', "Conflito detectado: {$conflito['mensagem']}");
        }

        // 2. Busca pacote ativo se cobrança for pacote
        $pacoteId = null;
        if ($validated['tipo_cobranca'] === 'pacote') {
            $pacoteAtivo = PacientePacote::where('paciente_id', $validated['paciente_id'])
                ->where('status', 'ativo')
                ->where('sessoes_restantes', '>', 0)
                ->where(fn($q) => $q->whereNull('data_validade')->orWhere('data_validade', '>=', $validated['data']))
                ->orderBy('data_validade', 'asc')
                ->first();

            if ($pacoteAtivo) {
                $pacoteId = $pacoteAtivo->id;
            }
        }

        $grupoRecorrencia = $request->filled('recorrencia') && $request->recorrencia !== 'nenhuma'
            ? (string) Str::uuid()
            : null;

        $qtdRecorrencias = $request->recorrencia !== 'nenhuma' ? (int) $request->get('quantidade_recorrencias', 1) : 1;

        $criados = 0;
        $conflitosRecorrencia = [];

        for ($i = 0; $i < $qtdRecorrencias; $i++) {
            $inicioSessao = match ($request->recorrencia) {
                'semanal' => $dataHoraInicio->copy()->addWeeks($i),
                'quinzenal' => $dataHoraInicio->copy()->addWeeks($i * 2),
                default => $dataHoraInicio->copy(),
            };
            $fimSessao = $inicioSessao->copy()->addMinutes($duracao);

            // Verifica conflito para cada data da série
            if ($i > 0) {
                $c = Agendamento::checkConflict(
                    $validated['profissional_id'],
                    $validated['sala_id'] ?? null,
                    $inicioSessao,
                    $fimSessao
                );
                if ($c) {
                    $conflitosRecorrencia[] = $inicioSessao->format('d/m/Y H:i');
                    continue; // Pula horário conflitante
                }
            }

            $agendamento = Agendamento::create([
                'paciente_id' => $validated['paciente_id'],
                'profissional_id' => $validated['profissional_id'],
                'sala_id' => $validated['sala_id'] ?? null,
                'servico_id' => $validated['servico_id'],
                'paciente_pacote_id' => $pacoteId,
                'data_hora_inicio' => $inicioSessao,
                'data_hora_fim' => $fimSessao,
                'duracao_minutos' => $duracao,
                'status' => 'agendado',
                'tipo_cobranca' => $validated['tipo_cobranca'],
                'observacoes' => $validated['observacoes'] ?? null,
                'recorrencia_grupo_id' => $grupoRecorrencia,
            ]);

            $criados++;
        }

        AuditService::log('agenda.create', "Agendou {$criados} atendimento(s) para o paciente ID {$validated['paciente_id']}");

        $msg = "{$criados} agendamento(s) realizado(s) com sucesso!";
        if (!empty($conflitosRecorrencia)) {
            $msg .= " Nota: os seguintes horários não foram agendados devido a conflito de agenda: " . implode(', ', $conflitosRecorrencia);
        }

        return redirect()->route('agenda.index', ['data' => $validated['data']])
            ->with('success', $msg);
    }

    public function updateStatus(Request $request, $id)
    {
        $agendamento = Agendamento::with(['paciente', 'profissional', 'servico', 'pacientePacote'])->findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:agendado,confirmado,em_espera,em_atendimento,concluido,falta,cancelado'],
            'motivo_cancelamento' => ['nullable', 'string', 'required_if:status,cancelado'],
        ]);

        $novoStatus = $request->status;
        $statusAnterior = $agendamento->status;

        $agendamento->status = $novoStatus;
        if ($novoStatus === 'cancelado') {
            $agendamento->motivo_cancelamento = $request->motivo_cancelamento;

            // Se estava concluído e foi cancelado, estorna sessão se tiver sido cobrada
            if ($statusAnterior === 'concluido' && $agendamento->pacientePacote) {
                $agendamento->pacientePacote->estornarSessao($agendamento, 'Estorno por cancelamento posterior de atendimento');
            }
        }

        // Se concluído: baixa de sessão automática do pacote e cálculo de repasse
        if ($novoStatus === 'concluido' && $statusAnterior !== 'concluido') {
            if ($agendamento->pacientePacote && $agendamento->pacientePacote->esta_valido) {
                $agendamento->pacientePacote->consumirSessao($agendamento, "Baixa por atendimento realizado em {$agendamento->data_hora_inicio->format('d/m/Y')}");
            }

            // Gera repasse profissional automático se configurado
            $this->gerarRepasseSeAplicavel($agendamento);
        }

        // Política de falta: cobra sessão se configurado
        if ($novoStatus === 'falta' && $statusAnterior !== 'falta') {
            $config = ClinicaConfiguracao::instance();
            if ($config->politica_falta_cobra_sessao && $agendamento->pacientePacote && $agendamento->pacientePacote->esta_valido) {
                $agendamento->pacientePacote->consumirSessao($agendamento, "Débito por falta não justificada conforme política da clínica");
            }
        }

        $agendamento->save();

        AuditService::log('agenda.status', "Alterou status do agendamento #{$agendamento->id} de '{$statusAnterior}' para '{$novoStatus}'", $agendamento);

        return back()->with('success', "Situação do agendamento atualizada para: " . ucfirst($novoStatus));
    }

    public function remarcar(Request $request, $id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $request->validate([
            'nova_data' => ['required', 'date'],
            'novo_horario' => ['required', 'date_format:H:i'],
            'sala_id' => ['nullable', 'exists:salas,id'],
        ]);

        $novaDataInicio = Carbon::parse("{$request->nova_data} {$request->novo_horario}");
        $novaDataFim = $novaDataInicio->copy()->addMinutes($agendamento->duracao_minutos);

        $conflito = Agendamento::checkConflict(
            $agendamento->profissional_id,
            $request->sala_id ?: $agendamento->sala_id,
            $novaDataInicio,
            $novaDataFim,
            $agendamento->id
        );

        if ($conflito) {
            return back()->with('error', "Não foi possível remarcar: {$conflito['mensagem']}");
        }

        $antigo = "{$agendamento->data_hora_inicio->format('d/m/Y H:i')}";
        $agendamento->data_hora_inicio = $novaDataInicio;
        $agendamento->data_hora_fim = $novaDataFim;
        if ($request->filled('sala_id')) {
            $agendamento->sala_id = $request->sala_id;
        }
        $agendamento->status = 'agendado';
        $agendamento->save();

        AuditService::log('agenda.remarcar', "Remarcou agendamento #{$agendamento->id} de {$antigo} para {$novaDataInicio->format('d/m/Y H:i')}", $agendamento);

        return back()->with('success', "Agendamento remarcado com sucesso para {$novaDataInicio->format('d/m/Y H:i')}!");
    }

    /**
     * Tela de atendimento ágil direto da agenda
     */
    public function atendimento($id)
    {
        $agendamento = Agendamento::with(['paciente', 'profissional', 'sala', 'servico', 'pacientePacote', 'evolucao'])->findOrFail($id);

        $paciente = $agendamento->paciente;
        $historicoEvolucoes = $paciente->evolucoes()->with('profissional')->orderBy('data_hora_evolucao', 'desc')->take(5)->get();

        return view('agenda.atendimento', compact('agendamento', 'paciente', 'historicoEvolucoes'));
    }

    public function storeBloqueio(Request $request)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'motivo' => ['nullable', 'string'],
            'profissional_id' => ['nullable', 'exists:users,id'],
            'sala_id' => ['nullable', 'exists:salas,id'],
            'data_inicio' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'data_fim' => ['required', 'date'],
            'hora_fim' => ['required', 'date_format:H:i'],
            'tipo' => ['required', 'in:feriado,recesso,intervalo,reuniao,outro'],
        ]);

        $inicio = Carbon::parse("{$validated['data_inicio']} {$validated['hora_inicio']}");
        $fim = Carbon::parse("{$validated['data_fim']} {$validated['hora_fim']}");

        if ($fim->lessThanOrEqualTo($inicio)) {
            return back()->with('error', 'O horário de término do bloqueio deve ser posterior ao horário de início.');
        }

        $bloqueio = BloqueioAgenda::create([
            'profissional_id' => $validated['profissional_id'] ?? null,
            'sala_id' => $validated['sala_id'] ?? null,
            'titulo' => $validated['titulo'],
            'motivo' => $validated['motivo'] ?? null,
            'data_hora_inicio' => $inicio,
            'data_hora_fim' => $fim,
            'tipo' => $validated['tipo'],
        ]);

        AuditService::log('agenda.bloqueio.create', "Cadastrou bloqueio de agenda: {$bloqueio->titulo}", $bloqueio);

        return back()->with('success', 'Bloqueio de agenda registrado com sucesso.');
    }

    public function destroyBloqueio($id)
    {
        $bloqueio = BloqueioAgenda::findOrFail($id);
        $titulo = $bloqueio->titulo;
        $bloqueio->delete();

        AuditService::log('agenda.bloqueio.delete', "Removeu bloqueio de agenda: {$titulo}");

        return back()->with('success', 'Bloqueio removido com sucesso.');
    }

    /**
     * Exportação de calendário padrão iCalendar (.ics)
     */
    public function exportIcal(Request $request)
    {
        $profissionalId = $request->get('profissional_id');

        $agendamentos = Agendamento::with(['paciente', 'profissional', 'sala', 'servico'])
            ->where('status', '!=', 'cancelado')
            ->where('data_hora_inicio', '>=', now()->subDays(7))
            ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
            ->get();

        $output = "BEGIN:VCALENDAR\r\n";
        $output .= "VERSION:2.0\r\n";
        $output .= "PRODID:-//Clinica Fabris//Agenda Fisioterapia//PT-BR\r\n";
        $output .= "CALSCALE:GREGORIAN\r\n";

        foreach ($agendamentos as $ag) {
            $dtStart = $ag->data_hora_inicio->format('Ymd\THis\Z');
            $dtEnd = $ag->data_hora_fim->format('Ymd\THis\Z');
            $summary = "{$ag->paciente->nome} - {$ag->servico->nome}";
            $location = $ag->sala ? $ag->sala->nome : 'Clínica Fabris';

            $output .= "BEGIN:VEVENT\r\n";
            $output .= "UID:agendamento-{$ag->id}@clinicafabris.com.br\r\n";
            $output .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $output .= "DTSTART:{$dtStart}\r\n";
            $output .= "DTEND:{$dtEnd}\r\n";
            $output .= "SUMMARY:{$summary}\r\n";
            $output .= "LOCATION:{$location}\r\n";
            $output .= "DESCRIPTION:Profissional: {$ag->profissional->name}\\nStatus: " . ucfirst($ag->status) . "\r\n";
            $output .= "END:VEVENT\r\n";
        }

        $output .= "END:VCALENDAR\r\n";

        return response($output, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="agenda-clinica-fabris.ics"',
        ]);
    }

    private function gerarRepasseSeAplicavel(Agendamento $agendamento): void
    {
        $servico = $agendamento->servico;
        if (!$servico) return;

        // Verifica se já existe repasse para este agendamento
        if (RepasseProfissional::where('agendamento_id', $agendamento->id)->exists()) {
            return;
        }

        $valorBase = (float) $servico->preco_padrao;
        $pct = (float) $servico->comissao_padrao_percentual;
        $fixo = (float) $servico->comissao_padrao_fixo;

        $valorRepasse = ($valorBase * ($pct / 100)) + $fixo;

        if ($valorRepasse > 0) {
            RepasseProfissional::create([
                'profissional_id' => $agendamento->profissional_id,
                'agendamento_id' => $agendamento->id,
                'descricao' => "Atendimento: {$agendamento->paciente->nome} ({$servico->nome})",
                'valor_base_servico' => $valorBase,
                'percentual_comissao' => $pct,
                'valor_fixo' => $fixo,
                'valor_repasse' => $valorRepasse,
                'data_competencia' => $agendamento->data_hora_inicio->format('Y-m-d'),
                'status' => 'pendente',
            ]);
        }
    }
}
