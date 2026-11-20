<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProntuarioEvolucao extends Model
{
    use SoftDeletes;

    protected $table = 'prontuario_evolucoes';

    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'agendamento_id',
        'data_hora_evolucao',
        'numero_sessao',
        'escala_dor_inicial',
        'relato_subjetivo',
        'procedimentos_realizados',
        'resposta_terapeutica',
        'escala_dor_final',
        'orientacoes_domiciliares',
        'finalizado',
        'finalizado_em',
        'assinatura_profissional',
    ];

    protected $casts = [
        'data_hora_evolucao' => 'datetime',
        'numero_sessao' => 'integer',
        'escala_dor_inicial' => 'integer',
        'escala_dor_final' => 'integer',
        'finalizado' => 'boolean',
        'finalizado_em' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_id');
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function aditivos(): HasMany
    {
        return $this->hasMany(ProntuarioAditivo::class, 'registro_id')
            ->where('registro_tipo', 'evolucao');
    }
}
