<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'data_nascimento',
        'sexo',
        'estado_civil',
        'profissao',
        'telefone',
        'whatsapp',
        'email',
        'contato_emergencia_nome',
        'contato_emergencia_telefone',
        'responsavel_legal_nome',
        'responsavel_legal_cpf',
        'responsavel_legal_parentesco',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'possui_convenio',
        'convenio_nome',
        'convenio_plano',
        'convenio_carteirinha',
        'convenio_validade',
        'origem_paciente',
        'observacoes_administrativas',
        'consentimento_lgpd',
        'consentimento_lgpd_em',
        'status',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'convenio_validade' => 'date',
        'possui_convenio' => 'boolean',
        'consentimento_lgpd' => 'boolean',
        'consentimento_lgpd_em' => 'datetime',
    ];

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(ProntuarioAvaliacao::class);
    }

    public function evolucoes(): HasMany
    {
        return $this->hasMany(ProntuarioEvolucao::class);
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function pacotes(): HasMany
    {
        return $this->hasMany(PacientePacote::class);
    }

    public function guiasConvenio(): HasMany
    {
        return $this->hasMany(ConvenioGuia::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoAnexo::class);
    }

    public function getIdadeAttribute(): ?int
    {
        return $this->data_nascimento ? $this->data_nascimento->age : null;
    }

    public function getCpfFormatadoAttribute(): ?string
    {
        if (!$this->cpf) return null;
        $cpf = preg_replace('/\D/', '', $this->cpf);
        if (strlen($cpf) !== 11) return $this->cpf;
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public function getTelefoneFormatadoAttribute(): ?string
    {
        $tel = preg_replace('/\D/', '', $this->whatsapp ?: $this->telefone);
        if (strlen($tel) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $tel);
        }
        if (strlen($tel) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $tel);
        }
        return $this->whatsapp ?: $this->telefone;
    }
}
