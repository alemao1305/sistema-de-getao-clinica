<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProntuarioAvaliacao extends Model
{
    use SoftDeletes;

    protected $table = 'prontuario_avaliacoes';

    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'agendamento_id',
        'modelo_avaliacao_id',
        'titulo',
        'data_avaliacao',
        'queixa_principal',
        'historia_doenca_atual',
        'historia_patologica_pregressa',
        'medicacoes_em_uso',
        'escala_dor_eva',
        'localizacao_dor',
        'caracteristica_dor',
        'fatores_melhora',
        'fatores_piora',
        'inspecao_palpacao',
        'amplitude_movimento',
        'forca_muscular',
        'testes_especiais_ortopedicos',
        'avaliacao_postural',
        'diagnostico_fisioterapeutico',
        'objetivos_terapeuticos',
        'plano_tratamento',
        'frequencia_semanal_sugerida',
        'finalizado',
        'finalizado_em',
        'assinatura_profissional',
    ];

    protected $casts = [
        'data_avaliacao' => 'date',
        'escala_dor_eva' => 'integer',
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

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloAvaliacao::class, 'modelo_avaliacao_id');
    }

    public function aditivos(): HasMany
    {
        return $this->hasMany(ProntuarioAditivo::class, 'registro_id')
            ->where('registro_tipo', 'avaliacao');
    }
}
