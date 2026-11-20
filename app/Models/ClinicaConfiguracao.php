<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicaConfiguracao extends Model
{
    protected $table = 'clinica_configuracoes';

    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'crefito_pj',
        'responsavel_tecnico',
        'telefone',
        'whatsapp',
        'email',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'horario_abertura',
        'horario_fechamento',
        'intervalo_agenda_minutos',
        'politica_cancelamento_horas',
        'politica_falta_cobra_sessao',
        'logo_path',
        'cabecalho_documento',
        'rodape_documento',
    ];

    protected $casts = [
        'politica_falta_cobra_sessao' => 'boolean',
        'intervalo_agenda_minutos' => 'integer',
        'politica_cancelamento_horas' => 'integer',
    ];

    public static function instance(): self
    {
        return self::firstOrCreate([], [
            'nome_fantasia' => 'Clínica Fabris',
            'razao_social' => 'Clínica Fabris Fisioterapia e Reabilitação Ltda',
            'cnpj' => '12.345.678/0001-90',
            'crefito_pj' => '12345-SP/PJ',
            'responsavel_tecnico' => 'Dra. Fabris - CREFITO-3/123456-F',
            'telefone' => '(11) 3456-7890',
            'whatsapp' => '(11) 98765-4321',
            'email' => 'contato@clinicafabris.com.br',
            'horario_abertura' => '07:00:00',
            'horario_fechamento' => '20:00:00',
            'intervalo_agenda_minutos' => 30,
            'politica_cancelamento_horas' => 24,
            'politica_falta_cobra_sessao' => true,
        ]);
    }
}
