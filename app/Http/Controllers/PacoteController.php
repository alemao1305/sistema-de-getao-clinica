<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacote;
use App\Models\Servico;
use App\Services\AuditService;

class PacoteController extends Controller
{
    public function index()
    {
        $pacotes = Pacote::with('servico')->orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        return view('configuracoes.pacotes.index', compact('pacotes', 'servicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'servico_id' => ['required', 'exists:servicos,id'],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'quantidade_sessoes' => ['required', 'integer', 'min:2', 'max:100'],
            'preco_total' => ['required', 'numeric', 'min:0'],
            'validade_dias' => ['required', 'integer', 'min:1', 'max:730'],
            'regras_utilizacao' => ['nullable', 'string'],
            'permite_parcelamento' => ['boolean'],
            'max_parcelas' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $validated['permite_parcelamento'] = $request->boolean('permite_parcelamento', true);

        $pacote = Pacote::create($validated);
        AuditService::log('pacotes.create', "Cadastrou o pacote comercial {$pacote->nome}", $pacote);

        return back()->with('success', 'Pacote comercial cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);

        $validated = $request->validate([
            'servico_id' => ['required', 'exists:servicos,id'],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'quantidade_sessoes' => ['required', 'integer', 'min:2', 'max:100'],
            'preco_total' => ['required', 'numeric', 'min:0'],
            'validade_dias' => ['required', 'integer', 'min:1', 'max:730'],
            'regras_utilizacao' => ['nullable', 'string'],
            'permite_parcelamento' => ['boolean'],
            'max_parcelas' => ['required', 'integer', 'min:1', 'max:12'],
            'ativo' => ['boolean'],
        ]);

        $validated['permite_parcelamento'] = $request->boolean('permite_parcelamento', true);
        $validated['ativo'] = $request->boolean('ativo', true);

        $pacote->update($validated);
        AuditService::log('pacotes.update', "Atualizou o pacote comercial {$pacote->nome}", $pacote);

        return back()->with('success', 'Pacote comercial atualizado com sucesso!');
    }
}
