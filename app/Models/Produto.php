<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    use SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'codigo_barras',
        'sku',
        'categoria',
        'descricao',
        'preco_custo',
        'preco_venda',
        'estoque_atual',
        'estoque_minimo',
        'unidade_medida',
        'ativo',
    ];

    protected $casts = [
        'preco_custo' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'estoque_atual' => 'integer',
        'estoque_minimo' => 'integer',
        'ativo' => 'boolean',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(ProdutoMovimentacao::class)->orderBy('created_at', 'desc');
    }

    public function getEstoqueBaixoAttribute(): bool
    {
        return $this->estoque_atual <= $this->estoque_minimo;
    }

    public function getMargemLucroPercentualAttribute(): float
    {
        if ($this->preco_custo <= 0) return 100.0;
        return round((($this->preco_venda - $this->preco_custo) / $this->preco_custo) * 100, 1);
    }

    public function temEstoque(int $quantidade = 1): bool
    {
        return $this->estoque_atual >= $quantidade;
    }

    /**
     * Registra movimentação no estoque com histórico auditável
     */
    public function movimentarEstoque(string $tipo, int $quantidade, string $motivo, ?int $userId = null, ?int $vendaId = null): self
    {
        $saldoAnterior = $this->estoque_atual;

        if (in_array($tipo, ['saida', 'venda'])) {
            $saldoNovo = max(0, $saldoAnterior - $quantidade);
        } elseif ($tipo === 'entrada') {
            $saldoNovo = $saldoAnterior + $quantidade;
        } else { // ajuste
            $saldoNovo = $quantidade; // define quantidade absoluta
        }

        $this->estoque_atual = $saldoNovo;
        $this->save();

        $this->movimentacoes()->create([
            'user_id' => $userId ?? auth()->id(),
            'venda_id' => $vendaId,
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'saldo_anterior' => $saldoAnterior,
            'saldo_novo' => $saldoNovo,
            'motivo' => $motivo,
        ]);

        return $this;
    }
}
