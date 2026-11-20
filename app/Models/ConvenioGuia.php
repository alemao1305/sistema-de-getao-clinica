<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvenioGuia extends Model
{
    use SoftDeletes;

    protected $table = 'convenio_guias';

    protected $fillable = [
        'convenio_id',
        'paciente_id',
        'servico_id',
        'numero_guia',
        'numero_carteirinha',
        'validade_guia',
        'sessoes_autorizadas',
        'sessoes_executadas',
        'valor_sessao_acordado',
        'valor_total_guia',
        'status',
        'valor_glosado',
        'motivo_glosa',
        'data_faturamento',
        'data_recebimento',
        'observacoes',
    ];

    protected $casts = [
        'validade_guia' => 'date',
        'data_faturamento' => 'date',
        'data_recebimento' => 'date',
        'sessoes_autorizadas' => 'integer',
        'sessoes_executadas' => 'integer',
        'valor_sessao_acordado' => 'decimal:2',
        'valor_total_guia' => 'decimal:2',
        'valor_glosado' => 'decimal:2',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function getSessoesRestantesAttribute(): int
    {
        return max(0, $this->sessoes_autorizadas - $this->sessoes_executadas);
    }
}
