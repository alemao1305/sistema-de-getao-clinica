<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sala;
use App\Services\AuditService;

class SalaController extends Controller
{
    public function index()
    {
        $salas = Sala::withCount('agendamentos')->orderBy('nome')->get();
        return view('configuracoes.salas.index', compact('salas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'cor' => ['nullable', 'string', 'max:10'],
            'capacidade_simultanea' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $validated['ativa'] = true;
        $sala = Sala::create($validated);
        AuditService::log('salas.create', "Cadastrou a sala {$sala->nome}", $sala);

        return back()->with('success', 'Sala de atendimento cadastrada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'cor' => ['nullable', 'string', 'max:10'],
            'capacidade_simultanea' => ['required', 'integer', 'min:1', 'max:10'],
            'ativa' => ['boolean'],
        ]);

        $validated['ativa'] = $request->boolean('ativa', true);
        $sala->update($validated);
        AuditService::log('salas.update', "Atualizou a sala {$sala->nome}", $sala);

        return back()->with('success', 'Dados da sala atualizados com sucesso!');
    }
}
