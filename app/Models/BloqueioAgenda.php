<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueioAgenda extends Model
{
    protected $table = 'bloqueios_agenda';

    protected $fillable = [
        'profissional_id',
        'sala_id',
        'titulo',
        'motivo',
        'data_hora_inicio',
        'data_hora_fim',
        'tipo',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
    ];

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_id');
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }
}
