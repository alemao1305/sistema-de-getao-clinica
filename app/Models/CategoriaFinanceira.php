<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaFinanceira extends Model
{
    protected $table = 'categorias_financeiras';

    protected $fillable = ['nome', 'tipo', 'cor'];

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class, 'categoria_id');
    }

    public function contasPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class, 'categoria_id');
    }
}
