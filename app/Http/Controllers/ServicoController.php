<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servico;
use App\Services\AuditService;

class ServicoController extends Controller
{
    public function index()
    {
        $servicos = Servico::withCount('pacotes')->orderBy('nome')->get();
        return view('configuracoes.servicos.index', compact('servicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:100'],
            'duracao_minutos' => ['required', 'integer', 'min:15', 'max:240'],
            'preco_padrao' => ['required', 'numeric', 'min:0'],
            'tipo' => ['required', 'in:avulso,pacote,plano,convenio'],
            'comissao_padrao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'comissao_padrao_fixo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $servico = Servico::create($validated);
        AuditService::log('servicos.create', "Cadastrou o serviço {$servico->nome}", $servico);

        return back()->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $servico = Servico::findOrFail($id);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:100'],
            'duracao_minutos' => ['required', 'integer', 'min:15', 'max:240'],
            'preco_padrao' => ['required', 'numeric', 'min:0'],
            'tipo' => ['required', 'in:avulso,pacote,plano,convenio'],
            'comissao_padrao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'comissao_padrao_fixo' => ['nullable', 'numeric', 'min:0'],
            'ativo' => ['boolean'],
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);

        $servico->update($validated);
        AuditService::log('servicos.update', "Atualizou o serviço {$servico->nome}", $servico);

        return back()->with('success', 'Serviço atualizado com sucesso!');
    }
}
