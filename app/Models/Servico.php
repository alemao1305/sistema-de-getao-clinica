<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'duracao_minutos',
        'preco_padrao',
        'tipo',
        'comissao_padrao_percentual',
        'comissao_padrao_fixo',
        'ativo',
    ];

    protected $casts = [
        'duracao_minutos' => 'integer',
        'preco_padrao' => 'decimal:2',
        'comissao_padrao_percentual' => 'decimal:2',
        'comissao_padrao_fixo' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function pacotes(): HasMany
    {
        return $this->hasMany(Pacote::class);
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }
}
