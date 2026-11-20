<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pacote extends Model
{
    protected $fillable = [
        'servico_id',
        'nome',
        'descricao',
        'quantidade_sessoes',
        'preco_total',
        'validade_dias',
        'regras_utilizacao',
        'permite_parcelamento',
        'max_parcelas',
        'ativo',
    ];

    protected $casts = [
        'quantidade_sessoes' => 'integer',
        'preco_total' => 'decimal:2',
        'validade_dias' => 'integer',
        'permite_parcelamento' => 'boolean',
        'max_parcelas' => 'integer',
        'ativo' => 'boolean',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function pacientesPacotes(): HasMany
    {
        return $this->hasMany(PacientePacote::class);
    }

    public function getValorPorSessaoAttribute(): float
    {
        if ($this->quantidade_sessoes > 0) {
            return (float) ($this->preco_total / $this->quantidade_sessoes);
        }
        return 0.0;
    }
}
