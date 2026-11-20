<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProntuarioAditivo extends Model
{
    protected $table = 'prontuario_aditivos';

    protected $fillable = [
        'registro_tipo',
        'registro_id',
        'user_id',
        'justificativa',
        'conteudo_aditivo',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
