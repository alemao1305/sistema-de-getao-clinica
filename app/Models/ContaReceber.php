<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    use SoftDeletes;

    protected $table = 'contas_receber';

    protected $fillable = [
        'venda_id',
        'paciente_id',
        'categoria_id',
        'forma_pagamento_id',
        'descricao',
        'valor_nominal',
        'taxa_operadora',
        'valor_recebido',
        'data_vencimento',
        'data_recebimento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'valor_nominal' => 'decimal:2',
        'taxa_operadora' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_recebimento' => 'date',
    ];

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
    }

    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pago' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'label' => 'Recebido'],
            'pendente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'label' => 'Pendente'],
            'parcial' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Parcial'],
            'cancelado' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Cancelado'],
            'estornado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Estornado'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($this->status)],
        };
    }
}
