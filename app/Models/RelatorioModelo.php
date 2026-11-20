<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioModelo extends Model
{
    protected $table = 'relatorio_modelos';

    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'fonte_dados',
        'colunas_selecionadas',
        'filtros_padrao',
        'agrupamento',
        'ordenar_por',
        'direcao_ordenacao',
        'favorito',
    ];

    protected $casts = [
        'colunas_selecionadas' => 'array',
        'filtros_padrao' => 'array',
        'favorito' => 'boolean',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
