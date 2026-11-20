<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoAnexo extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_anexos';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'prontuario_id',
        'titulo',
        'tipo_documento',
        'arquivo_path',
        'nome_original',
        'mime_type',
        'tamanho_bytes',
        'observacoes',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTipoFormatadoAttribute(): string
    {
        return match ($this->tipo_documento) {
            'exame_imagem' => 'Exame de Imagem (RX / RM / TC)',
            'laudo' => 'Laudo Médico / Pericial',
            'atestado' => 'Atestado / Declaração',
            'consentimento_lgpd' => 'Termo LGPD / Consentimento',
            'encaminhamento' => 'Encaminhamento Médico',
            default => 'Outro Documento',
        };
    }
}
