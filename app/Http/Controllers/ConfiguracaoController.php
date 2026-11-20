<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicaConfiguracao;
use App\Services\AuditService;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $config = ClinicaConfiguracao::instance();
        return view('configuracoes.index', compact('config'));
    }

    public function update(Request $request)
    {
        $config = ClinicaConfiguracao::instance();

        $validated = $request->validate([
            'nome_fantasia' => ['required', 'string', 'max:255'],
            'razao_social' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'crefito_pj' => ['nullable', 'string', 'max:50'],
            'responsavel_tecnico' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'horario_abertura' => ['required'],
            'horario_fechamento' => ['required'],
            'intervalo_agenda_minutos' => ['required', 'integer', 'min:10', 'max:120'],
            'politica_cancelamento_horas' => ['required', 'integer', 'min:0'],
            'politica_falta_cobra_sessao' => ['boolean'],
            'cabecalho_documento' => ['nullable', 'string'],
            'rodape_documento' => ['nullable', 'string'],
        ]);

        $validated['politica_falta_cobra_sessao'] = $request->boolean('politica_falta_cobra_sessao', true);

        $old = $config->toArray();
        $config->update($validated);

        AuditService::log('configuracoes.update', "Atualizou as configurações institucionais da clínica", $config, $old, $config->toArray());

        return back()->with('success', 'Configurações da clínica atualizadas com sucesso!');
    }
}
