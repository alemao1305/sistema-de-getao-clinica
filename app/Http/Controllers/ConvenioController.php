<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Convenio;
use App\Models\ConvenioGuia;
use App\Models\Paciente;
use App\Models\Servico;
use App\Services\AuditService;

class ConvenioController extends Controller
{
    public function index(Request $request)
    {
        $aba = $request->get('aba', 'guias'); // guias, operadoras
        $convenios = Convenio::withCount('guias')->orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->get();
        $pacientes = Paciente::where('status', 'ativo')->orderBy('nome')->get();

        $guias = ConvenioGuia::with(['convenio', 'paciente', 'servico'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('convenios.index', compact('aba', 'convenios', 'guias', 'servicos', 'pacientes'));
    }

    public function storeConvenio(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'registro_ans' => ['nullable', 'string', 'max:50'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'prazo_pagamento_dias' => ['required', 'integer', 'min:0'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $convenio = Convenio::create($validated);
        AuditService::log('convenios.create', "Cadastrou a operadora de convênio {$convenio->nome}", $convenio);

        return back()->with('success', 'Operadora de convênio cadastrada com sucesso!');
    }

    public function storeGuia(Request $request)
    {
        $validated = $request->validate([
            'convenio_id' => ['required', 'exists:convenios,id'],
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'servico_id' => ['nullable', 'exists:servicos,id'],
            'numero_guia' => ['required', 'string', 'max:100'],
            'numero_carteirinha' => ['required', 'string', 'max:100'],
            'validade_guia' => ['required', 'date'],
            'sessoes_autorizadas' => ['required', 'integer', 'min:1'],
            'valor_sessao_acordado' => ['required', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $total = $validated['sessoes_autorizadas'] * $validated['valor_sessao_acordado'];

        $guia = ConvenioGuia::create([
            'convenio_id' => $validated['convenio_id'],
            'paciente_id' => $validated['paciente_id'],
            'servico_id' => $validated['servico_id'] ?? null,
            'numero_guia' => $validated['numero_guia'],
            'numero_carteirinha' => $validated['numero_carteirinha'],
            'validade_guia' => $validated['validade_guia'],
            'sessoes_autorizadas' => $validated['sessoes_autorizadas'],
            'sessoes_executadas' => 0,
            'valor_sessao_acordado' => $validated['valor_sessao_acordado'],
            'valor_total_guia' => $total,
            'status' => 'autorizada',
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        AuditService::log('convenios.guia.create', "Cadastrou a guia de convênio nº {$guia->numero_guia}", $guia);

        return back()->with('success', 'Guia de autorização cadastrada com sucesso!');
    }

    public function atualizarGuia(Request $request, $id)
    {
        $guia = ConvenioGuia::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:autorizada,em_execucao,concluida,faturada,paga,glosada'],
            'valor_glosado' => ['nullable', 'numeric', 'min:0'],
            'motivo_glosa' => ['nullable', 'string'],
            'sessoes_executadas' => ['nullable', 'integer', 'min:0'],
        ]);

        $guia->status = $validated['status'];
        if (isset($validated['sessoes_executadas'])) {
            $guia->sessoes_executadas = $validated['sessoes_executadas'];
        }
        if ($validated['status'] === 'glosada') {
            $guia->valor_glosado = $validated['valor_glosado'] ?? $guia->valor_total_guia;
            $guia->motivo_glosa = $validated['motivo_glosa'] ?? 'Glosa informada pela operadora';
        }

        $guia->save();
        AuditService::log('convenios.guia.update', "Atualizou status da guia nº {$guia->numero_guia} para '{$guia->status}'", $guia);

        return back()->with('success', 'Guia de convênio atualizada com sucesso!');
    }
}
