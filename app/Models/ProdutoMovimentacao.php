<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoMovimentacao extends Model
{
    protected $table = 'produto_movimentacoes';

    protected $fillable = [
        'produto_id',
        'user_id',
        'venda_id',
        'tipo',
        'quantidade',
        'saldo_anterior',
        'saldo_novo',
        'motivo',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'saldo_anterior' => 'integer',
        'saldo_novo' => 'integer',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }
}
