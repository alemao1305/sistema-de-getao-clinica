<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendaPagamento extends Model
{
    protected $table = 'venda_pagamentos';

    protected $fillable = [
        'venda_id',
        'forma_pagamento',
        'valor',
        'parcelas',
        'status',
        'data_vencimento',
        'data_pagamento',
        'comprovante_autorizacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'parcelas' => 'integer',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function getFormaPagamentoLabelAttribute(): string
    {
        return match ($this->forma_pagamento) {
            'pix' => 'Pix',
            'dinheiro' => 'Dinheiro',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'boleto' => 'Boleto',
            'transferencia' => 'Transferência / TED',
            'convenio' => 'Convênio',
            default => ucfirst($this->forma_pagamento),
        };
    }
}
