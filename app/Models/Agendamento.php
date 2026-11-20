<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'sala_id',
        'servico_id',
        'paciente_pacote_id',
        'data_hora_inicio',
        'data_hora_fim',
        'duracao_minutos',
        'status',
        'tipo_cobranca',
        'motivo_cancelamento',
        'observacoes',
        'recorrencia_grupo_id',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'duracao_minutos' => 'integer',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_id');
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function pacientePacote(): BelongsTo
    {
        return $this->belongsTo(PacientePacote::class);
    }

    public function avaliacao(): HasOne
    {
        return $this->hasOne(ProntuarioAvaliacao::class);
    }

    public function evolucao(): HasOne
    {
        return $this->hasOne(ProntuarioEvolucao::class);
    }

    public function repasse(): HasOne
    {
        return $this->hasOne(RepasseProfissional::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'agendado' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Agendado'],
            'confirmado' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'label' => 'Confirmado'],
            'em_espera' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'label' => 'Em Espera'],
            'em_atendimento' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Em Atendimento'],
            'concluido' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-800', 'label' => 'Concluído'],
            'falta' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Falta'],
            'cancelado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Cancelado'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($this->status)],
        };
    }

    /**
     * Detecta conflitos de horário para profissional ou sala
     * Retorna array com os detalhes do conflito ou null se liberado
     */
    public static function checkConflict(
        int $profissionalId,
        ?int $salaId,
        Carbon|string $inicio,
        Carbon|string $fim,
        ?int $exceptId = null
    ): ?array {
        $inicio = Carbon::parse($inicio);
        $fim = Carbon::parse($fim);

        // 1. Conflito de profissional na agenda
        $conflitoProfissional = self::where('profissional_id', $profissionalId)
            ->whereNotIn('status', ['cancelado'])
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('data_hora_inicio', [$inicio, $fim->copy()->subSecond()])
                  ->orWhereBetween('data_hora_fim', [$inicio->copy()->addSecond(), $fim])
                  ->orWhere(function ($sub) use ($inicio, $fim) {
                      $sub->where('data_hora_inicio', '<=', $inicio)
                          ->where('data_hora_fim', '>=', $fim);
                  });
            })
            ->with(['paciente', 'profissional'])
            ->first();

        if ($conflitoProfissional) {
            return [
                'tipo' => 'profissional',
                'mensagem' => "O profissional {$conflitoProfissional->profissional->name} já possui agendamento com {$conflitoProfissional->paciente->nome} das {$conflitoProfissional->data_hora_inicio->format('H:i')} às {$conflitoProfissional->data_hora_fim->format('H:i')}.",
            ];
        }

        // 2. Conflito de sala na agenda
        if ($salaId) {
            $sala = Sala::find($salaId);
            $capacidade = $sala ? $sala->capacidade_simultanea : 1;

            $agendamentosSalaCount = self::where('sala_id', $salaId)
                ->whereNotIn('status', ['cancelado'])
                ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
                ->where(function ($q) use ($inicio, $fim) {
                    $q->whereBetween('data_hora_inicio', [$inicio, $fim->copy()->subSecond()])
                      ->orWhereBetween('data_hora_fim', [$inicio->copy()->addSecond(), $fim])
                      ->orWhere(function ($sub) use ($inicio, $fim) {
                          $sub->where('data_hora_inicio', '<=', $inicio)
                              ->where('data_hora_fim', '>=', $fim);
                      });
                })
                ->count();

            if ($agendamentosSalaCount >= $capacidade) {
                return [
                    'tipo' => 'sala',
                    'mensagem' => "A sala {$sala->nome} atingiu sua capacidade máxima ({$capacidade} paciente(s)) neste horário.",
                ];
            }
        }

        // 3. Bloqueios de agenda do profissional ou da clínica
        $bloqueio = BloqueioAgenda::where(function ($q) use ($profissionalId, $salaId) {
                $q->whereNull('profissional_id') // Bloqueio geral da clínica
                  ->orWhere('profissional_id', $profissionalId)
                  ->when($salaId, fn($sq) => $sq->orWhere('sala_id', $salaId));
            })
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('data_hora_inicio', [$inicio, $fim->copy()->subSecond()])
                  ->orWhereBetween('data_hora_fim', [$inicio->copy()->addSecond(), $fim])
                  ->orWhere(function ($sub) use ($inicio, $fim) {
                      $sub->where('data_hora_inicio', '<=', $inicio)
                          ->where('data_hora_fim', '>=', $fim);
                  });
            })
            ->first();

        if ($bloqueio) {
            return [
                'tipo' => 'bloqueio',
                'mensagem' => "Existe um bloqueio de agenda cadastrado: \"{$bloqueio->titulo}\" ({$bloqueio->motivo}).",
            ];
        }

        return null; // Sem conflito
    }
}
