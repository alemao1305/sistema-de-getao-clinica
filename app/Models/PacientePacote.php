<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class PacientePacote extends Model
{
    protected $table = 'paciente_pacotes';

    protected $fillable = [
        'paciente_id',
        'venda_id',
        'pacote_id',
        'servico_id',
        'nome_pacote',
        'sessoes_totais',
        'sessoes_utilizadas',
        'sessoes_restantes',
        'sessoes_bonus',
        'data_inicio',
        'data_validade',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'sessoes_totais' => 'integer',
        'sessoes_utilizadas' => 'integer',
        'sessoes_restantes' => 'integer',
        'sessoes_bonus' => 'integer',
        'data_inicio' => 'date',
        'data_validade' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function pacote(): BelongsTo
    {
        return $this->belongsTo(Pacote::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function consumos(): HasMany
    {
        return $this->hasMany(PacientePacoteConsumo::class);
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function getEstaValidoAttribute(): bool
    {
        if ($this->status !== 'ativo') return false;
        if ($this->sessoes_restantes <= 0) return false;
        if ($this->data_validade && $this->data_validade->isPast()) return false;
        return true;
    }

    /**
     * Dá baixa em uma sessão do pacote e registra o consumo auditável
     */
    public function consumirSessao(?Agendamento $agendamento = null, string $motivo = 'Baixa por atendimento realizado'): bool
    {
        if ($this->sessoes_restantes <= 0) {
            return false;
        }

        $this->sessoes_utilizadas += 1;
        $this->sessoes_restantes -= 1;

        if ($this->sessoes_restantes <= 0) {
            $this->status = 'concluido';
        }

        $this->save();

        // Registra histórico de consumo
        $this->consumos()->create([
            'agendamento_id' => $agendamento?->id,
            'user_id' => Auth::id(),
            'tipo' => 'baixa_atendimento',
            'quantidade' => 1,
            'motivo' => $motivo,
        ]);

        return true;
    }

    /**
     * Estorna uma sessão previamente consumida
     */
    public function estornarSessao(?Agendamento $agendamento = null, string $motivo = 'Estorno de sessão cancelada/remarcada'): bool
    {
        if ($this->sessoes_utilizadas <= 0) {
            return false;
        }

        $this->sessoes_utilizadas -= 1;
        $this->sessoes_restantes += 1;

        if ($this->status === 'concluido') {
            $this->status = 'ativo';
        }

        $this->save();

        // Registra histórico de estorno
        $this->consumos()->create([
            'agendamento_id' => $agendamento?->id,
            'user_id' => Auth::id(),
            'tipo' => 'estorno',
            'quantidade' => -1,
            'motivo' => $motivo,
        ]);

        return true;
    }
}
