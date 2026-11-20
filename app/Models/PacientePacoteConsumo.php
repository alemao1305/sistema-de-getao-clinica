<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacientePacoteConsumo extends Model
{
    protected $table = 'paciente_pacote_consumos';

    protected $fillable = [
        'paciente_pacote_id',
        'agendamento_id',
        'user_id',
        'tipo',
        'quantidade',
        'motivo',
    ];

    public function pacote(): BelongsTo
    {
        return $this->belongsTo(PacientePacote::class, 'paciente_pacote_id');
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
