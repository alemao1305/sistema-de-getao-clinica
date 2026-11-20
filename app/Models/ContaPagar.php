<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaPagar extends Model
{
    use SoftDeletes;

    protected $table = 'contas_pagar';

    protected $fillable = [
        'categoria_id',
        'descricao',
        'fornecedor',
        'centro_custo',
        'valor',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'status',
        'comprovante_path',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pago' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'label' => 'Pago'],
            'pendente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'label' => 'A Pagar'],
            'cancelado' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Cancelado'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($this->status)],
        };
    }
}
