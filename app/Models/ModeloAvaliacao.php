<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeloAvaliacao extends Model
{
    protected $table = 'modelos_avaliacao';

    protected $fillable = [
        'nome',
        'especialidade',
        'campos_estrutura',
        'ativo',
    ];

    protected $casts = [
        'campos_estrutura' => 'array',
        'ativo' => 'boolean',
    ];

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(ProntuarioAvaliacao::class);
    }
}
