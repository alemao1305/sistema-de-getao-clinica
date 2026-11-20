<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'paciente_id',
        'user_id',
        'data_venda',
        'valor_subtotal',
        'valor_desconto',
        'valor_total',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_venda' => 'date',
        'valor_subtotal' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(VendaPagamento::class);
    }

    public function pacotesGerados(): HasMany
    {
        return $this->hasMany(PacientePacote::class);
    }

    public function contaReceber(): HasOne
    {
        return $this->hasOne(ContaReceber::class);
    }

    public static function gerarCodigo(): string
    {
        $ano = date('Y');
        $ultimoId = (int) self::max('id') + 1;
        return 'VND-' . $ano . '-' . str_pad($ultimoId, 4, '0', STR_PAD_LEFT);
    }
}
