<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Convenio extends Model
{
    protected $fillable = [
        'nome',
        'registro_ans',
        'cnpj',
        'telefone',
        'email',
        'prazo_pagamento_dias',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'prazo_pagamento_dias' => 'integer',
        'ativo' => 'boolean',
    ];

    public function guias(): HasMany
    {
        return $this->hasMany(ConvenioGuia::class);
    }
}
