<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPagamento extends Model
{
    protected $table = 'formas_pagamento';

    protected $fillable = [
        'nome',
        'slug',
        'taxa_percentual',
        'prazo_dias_compensacao',
        'ativo',
    ];

    protected $casts = [
        'taxa_percentual' => 'decimal:2',
        'prazo_dias_compensacao' => 'integer',
        'ativo' => 'boolean',
    ];

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class);
    }
}
