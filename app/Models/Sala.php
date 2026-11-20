<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sala extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'cor',
        'capacidade_simultanea',
        'ativa',
    ];

    protected $casts = [
        'capacidade_simultanea' => 'integer',
        'ativa' => 'boolean',
    ];

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }
}
