<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RelatorioModelo;
use App\Models\Agendamento;
use App\Models\Venda;
use App\Models\PacientePacote;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\RepasseProfissional;
use App\Models\User;
use App\Models\Sala;
use App\Models\Servico;
use App\Models\Paciente;
use App\Models\Convenio;
use App\Models\ClinicaConfiguracao;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $fonteDados = $request->get('fonte', 'agendamentos'); // agendamentos, vendas, sessoes, financeiro_receitas, financeiro_despesas, repasses
        $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->endOfMonth()->format('Y-m-d'));
        
        $profissionalId = $request->get('profissional_id');
        $salaId = $request->get('sala_id');
        $pacienteId = $request->get('paciente_id');
        $status = $request->get('status');

        $colunasSelecionadas = $request->get('colunas', $this->getDefaultColunas($fonteDados));

        // Executa a consulta segura conforme fonte de dados
        $resultado = $this->executarConsulta(
            $fonteDados,
            $dataInicio,
            $dataFim,
            $profissionalId,
            $salaId,
            $pacienteId,
            $status,
            $colunasSelecionadas
        );

        $modelosSalvos = RelatorioModelo::where('user_id', Auth::id())
            ->orWhere('favorito', true)
            ->get();

        $profissionais = User::whereIn('role', ['fisioterapeuta', 'proprietaria'])->where('is_active', true)->get();
        $salas = Sala::where('ativa', true)->get();
        $pacientes = Paciente::where('status', 'ativo')->orderBy('nome')->get();
        $colunasDisponiveis = $this->getColunasDisponiveis($fonteDados);

        return view('relatorios.index', compact(
            'fonteDados',
            'dataInicio',
            'dataFim',
            'profissionalId',
            'salaId',
            'pacienteId',
            'status',
            'colunasSelecionadas',
            'colunasDisponiveis',
            'resultado',
            'modelosSalvos',
            'profissionais',
            'salas',
            'pacientes'
        ));
    }

    public function salvarModelo(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'fonte_dados' => ['required', 'string'],
            'colunas_selecionadas' => ['required', 'array'],
            'favorito' => ['nullable', 'boolean'],
        ]);

        $filtros = $request->only(['data_inicio', 'data_fim', 'profissional_id', 'sala_id', 'paciente_id', 'status']);

        RelatorioModelo::create([
            'user_id' => Auth::id(),
            'nome' => $validated['nome'],
            'fonte_dados' => $validated['fonte_dados'],
            'colunas_selecionadas' => $validated['colunas_selecionadas'],
            'filtros_padrao' => $filtros,
            'favorito' => $request->boolean('favorito'),
        ]);

        return back()->with('success', 'Modelo de relatório salvo com sucesso!');
    }

    public function exportarCsv(Request $request)
    {
        $fonteDados = $request->get('fonte', 'agendamentos');
        $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->endOfMonth()->format('Y-m-d'));
        $colunasSelecionadas = $request->get('colunas', $this->getDefaultColunas($fonteDados));

        $resultado = $this->executarConsulta(
            $fonteDados,
            $dataInicio,
            $dataFim,
            $request->get('profissional_id'),
            $request->get('sala_id'),
            $request->get('paciente_id'),
            $request->get('status'),
            $colunasSelecionadas,
            false // sem paginação para exportação
        );

        $colunasDisponiveis = $this->getColunasDisponiveis($fonteDados);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"relatorio-{$fonteDados}-" . date('Ymd-His') . ".csv\"",
        ];

        $callback = function () use ($resultado, $colunasSelecionadas, $colunasDisponiveis) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para abrir perfeitamente no Excel brasileiro
            fputs($handle, "\xEF\xBB\xBF");

            // Cabeçalhos CSV
            $headerRow = [];
            foreach ($colunasSelecionadas as $colKey) {
                $headerRow[] = $colunasDisponiveis[$colKey]['label'] ?? $colKey;
            }
            fputcsv($handle, $headerRow, ';');

            // Linhas de dados
            foreach ($resultado['dados'] as $linha) {
                $row = [];
                foreach ($colunasSelecionadas as $colKey) {
                    $row[] = $linha[$colKey] ?? '';
                }
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function imprimirPdf(Request $request)
    {
        $fonteDados = $request->get('fonte', 'agendamentos');
        $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->endOfMonth()->format('Y-m-d'));
        $colunasSelecionadas = $request->get('colunas', $this->getDefaultColunas($fonteDados));

        $resultado = $this->executarConsulta(
            $fonteDados,
            $dataInicio,
            $dataFim,
            $request->get('profissional_id'),
            $request->get('sala_id'),
            $request->get('paciente_id'),
            $request->get('status'),
            $colunasSelecionadas,
            false
        );

        $colunasDisponiveis = $this->getColunasDisponiveis($fonteDados);
        $config = ClinicaConfiguracao::instance();

        return view('relatorios.imprimir', compact(
            'fonteDados',
            'dataInicio',
            'dataFim',
            'colunasSelecionadas',
            'colunasDisponiveis',
            'resultado',
            'config'
        ));
    }

    private function getColunasDisponiveis(string $fonte): array
    {
        return match ($fonte) {
            'agendamentos' => [
                'data_hora' => ['label' => 'Data / Horário'],
                'paciente' => ['label' => 'Paciente'],
                'profissional' => ['label' => 'Fisioterapeuta'],
                'sala' => ['label' => 'Sala'],
                'servico' => ['label' => 'Serviço'],
                'cobranca' => ['label' => 'Tipo Cobrança'],
                'status' => ['label' => 'Situação'],
            ],
            'vendas' => [
                'codigo' => ['label' => 'Código Venda'],
                'data' => ['label' => 'Data da Venda'],
                'paciente' => ['label' => 'Paciente'],
                'itens' => ['label' => 'Itens'],
                'subtotal' => ['label' => 'Subtotal'],
                'desconto' => ['label' => 'Desconto'],
                'total' => ['label' => 'Valor Total'],
                'vendedor' => ['label' => 'Vendedor(a)'],
            ],
            'sessoes' => [
                'paciente' => ['label' => 'Paciente'],
                'pacote' => ['label' => 'Pacote'],
                'totais' => ['label' => 'Sessões Totais'],
                'utilizadas' => ['label' => 'Utilizadas'],
                'restantes' => ['label' => 'Restantes'],
                'validade' => ['label' => 'Data de Validade'],
                'status' => ['label' => 'Situação'],
            ],
            'financeiro_receitas' => [
                'descricao' => ['label' => 'Descrição'],
                'paciente' => ['label' => 'Paciente'],
                'vencimento' => ['label' => 'Data Vencimento'],
                'recebimento' => ['label' => 'Data Recebimento'],
                'valor' => ['label' => 'Valor Recebido'],
                'status' => ['label' => 'Situação'],
            ],
            'financeiro_despesas' => [
                'descricao' => ['label' => 'Descrição'],
                'fornecedor' => ['label' => 'Fornecedor'],
                'centro_custo' => ['label' => 'Centro de Custo'],
                'vencimento' => ['label' => 'Data Vencimento'],
                'pagamento' => ['label' => 'Data Pagamento'],
                'valor' => ['label' => 'Valor Pago'],
                'status' => ['label' => 'Situação'],
            ],
            'repasses' => [
                'profissional' => ['label' => 'Fisioterapeuta'],
                'descricao' => ['label' => 'Atendimento'],
                'competencia' => ['label' => 'Competência'],
                'base' => ['label' => 'Valor Base'],
                'valor_repasse' => ['label' => 'Valor Repasse'],
                'status' => ['label' => 'Situação'],
            ],
            default => [],
        };
    }

    private function getDefaultColunas(string $fonte): array
    {
        return array_keys($this->getColunasDisponiveis($fonte));
    }

    private function executarConsulta(
        string $fonte,
        string $inicio,
        string $fim,
        ?string $profissionalId,
        ?string $salaId,
        ?string $pacienteId,
        ?string $status,
        array $colunas,
        bool $paginar = true
    ): array {
        $dados = [];
        $totais = ['quantidade' => 0, 'valor' => 0.0];

        $dtInicio = Carbon::parse($inicio)->startOfDay();
        $dtFim = Carbon::parse($fim)->endOfDay();

        if ($fonte === 'agendamentos') {
            $query = Agendamento::with(['paciente', 'profissional', 'sala', 'servico'])
                ->whereBetween('data_hora_inicio', [$dtInicio, $dtFim])
                ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
                ->when($salaId, fn($q) => $q->where('sala_id', $salaId))
                ->when($pacienteId, fn($q) => $q->where('paciente_id', $pacienteId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('data_hora_inicio', 'asc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();

            foreach ($registros as $r) {
                $dados[] = [
                    'data_hora' => $r->data_hora_inicio->format('d/m/Y H:i'),
                    'paciente' => $r->paciente->nome,
                    'profissional' => $r->profissional->name,
                    'sala' => $r->sala->nome ?? '-',
                    'servico' => $r->servico->nome ?? '-',
                    'cobranca' => ucfirst($r->tipo_cobranca),
                    'status' => ucfirst($r->status),
                ];
            }
        } elseif ($fonte === 'vendas') {
            $query = Venda::with(['paciente', 'vendedor', 'itens'])
                ->whereBetween('data_venda', [$dtInicio->format('Y-m-d'), $dtFim->format('Y-m-d')])
                ->when($pacienteId, fn($q) => $q->where('paciente_id', $pacienteId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('data_venda', 'desc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();
            $totais['valor'] = $registros->sum('valor_total');

            foreach ($registros as $r) {
                $dados[] = [
                    'codigo' => $r->codigo,
                    'data' => $r->data_venda->format('d/m/Y'),
                    'paciente' => $r->paciente->nome,
                    'itens' => $r->itens->pluck('descricao')->implode(', '),
                    'subtotal' => 'R$ ' . number_format($r->valor_subtotal, 2, ',', '.'),
                    'desconto' => 'R$ ' . number_format($r->valor_desconto, 2, ',', '.'),
                    'total' => 'R$ ' . number_format($r->valor_total, 2, ',', '.'),
                    'vendedor' => $r->vendedor->name,
                ];
            }
        } elseif ($fonte === 'sessoes') {
            $query = PacientePacote::with(['paciente', 'servico'])
                ->when($pacienteId, fn($q) => $q->where('paciente_id', $pacienteId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('created_at', 'desc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();
            $totais['total_sessoes_restantes'] = $registros->sum('sessoes_restantes');

            foreach ($registros as $r) {
                $dados[] = [
                    'paciente' => $r->paciente->nome,
                    'pacote' => $r->nome_pacote,
                    'totais' => $r->sessoes_totais,
                    'utilizadas' => $r->sessoes_utilizadas,
                    'restantes' => $r->sessoes_restantes,
                    'validade' => $r->data_validade->format('d/m/Y'),
                    'status' => ucfirst($r->status),
                ];
            }
        } elseif ($fonte === 'financeiro_receitas') {
            $query = ContaReceber::with(['paciente', 'categoria'])
                ->whereBetween('data_vencimento', [$dtInicio->format('Y-m-d'), $dtFim->format('Y-m-d')])
                ->when($pacienteId, fn($q) => $q->where('paciente_id', $pacienteId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('data_vencimento', 'asc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();
            $totais['valor'] = $registros->sum('valor_nominal');

            foreach ($registros as $r) {
                $dados[] = [
                    'descricao' => $r->descricao,
                    'paciente' => $r->paciente->nome ?? '-',
                    'vencimento' => $r->data_vencimento->format('d/m/Y'),
                    'recebimento' => $r->data_recebimento ? $r->data_recebimento->format('d/m/Y') : '-',
                    'valor' => 'R$ ' . number_format($r->valor_recebido ?: $r->valor_nominal, 2, ',', '.'),
                    'status' => ucfirst($r->status),
                ];
            }
        } elseif ($fonte === 'financeiro_despesas') {
            $query = ContaPagar::with('categoria')
                ->whereBetween('data_vencimento', [$dtInicio->format('Y-m-d'), $dtFim->format('Y-m-d')])
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('data_vencimento', 'asc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();
            $totais['valor'] = $registros->sum('valor');

            foreach ($registros as $r) {
                $dados[] = [
                    'descricao' => $r->descricao,
                    'fornecedor' => $r->fornecedor ?: '-',
                    'centro_custo' => $r->centro_custo,
                    'vencimento' => $r->data_vencimento->format('d/m/Y'),
                    'pagamento' => $r->data_pagamento ? $r->data_pagamento->format('d/m/Y') : '-',
                    'valor' => 'R$ ' . number_format($r->valor_pago ?: $r->valor, 2, ',', '.'),
                    'status' => ucfirst($r->status),
                ];
            }
        } elseif ($fonte === 'repasses') {
            $query = RepasseProfissional::with(['profissional', 'agendamento.paciente'])
                ->whereBetween('data_competencia', [$dtInicio->format('Y-m-d'), $dtFim->format('Y-m-d')])
                ->when($profissionalId, fn($q) => $q->where('profissional_id', $profissionalId))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('data_competencia', 'asc');

            $registros = $query->get();
            $totais['quantidade'] = $registros->count();
            $totais['valor'] = $registros->sum('valor_repasse');

            foreach ($registros as $r) {
                $dados[] = [
                    'profissional' => $r->profissional->name,
                    'descricao' => $r->descricao,
                    'competencia' => $r->data_competencia->format('d/m/Y'),
                    'base' => 'R$ ' . number_format($r->valor_base_servico, 2, ',', '.'),
                    'valor_repasse' => 'R$ ' . number_format($r->valor_repasse, 2, ',', '.'),
                    'status' => ucfirst($r->status),
                ];
            }
        }

        return ['dados' => $dados, 'totais' => $totais];
    }
}
