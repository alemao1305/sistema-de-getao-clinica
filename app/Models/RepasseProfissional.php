<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepasseProfissional extends Model
{
    protected $table = 'repasses_profissionais';

    protected $fillable = [
        'profissional_id',
        'agendamento_id',
        'descricao',
        'valor_base_servico',
        'percentual_comissao',
        'valor_fixo',
        'valor_repasse',
        'data_competencia',
        'data_pagamento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'valor_base_servico' => 'decimal:2',
        'percentual_comissao' => 'decimal:2',
        'valor_fixo' => 'decimal:2',
        'valor_repasse' => 'decimal:2',
        'data_competencia' => 'date',
        'data_pagamento' => 'date',
    ];

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_id');
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }
}
