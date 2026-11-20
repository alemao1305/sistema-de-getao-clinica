<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Convenio;
use App\Services\AuditService;
use Illuminate\Validation\Rule;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->get('busca');
        $status = $request->get('status');
        $convenio = $request->get('convenio');

        $pacientes = Paciente::query()
            ->when($busca, function ($q) use ($busca) {
                $termoLimpo = preg_replace('/\D/', '', $busca);
                $q->where(function ($sub) use ($busca, $termoLimpo) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('whatsapp', 'like', "%{$busca}%");
                    if (!empty($termoLimpo)) {
                        $sub->orWhere('cpf', 'like', "%{$termoLimpo}%");
                    }
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($convenio !== null && $convenio !== '', function ($q) use ($convenio) {
                if ($convenio === 'sim') {
                    $q->where('possui_convenio', true);
                } elseif ($convenio === 'nao') {
                    $q->where('possui_convenio', false);
                }
            })
            ->orderBy('nome', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'busca', 'status', 'convenio'));
    }

    public function create()
    {
        $convenios = Convenio::where('ativo', true)->orderBy('nome')->get();
        return view('pacientes.create', compact('convenios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:18', 'unique:pacientes,cpf'],
            'rg' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:M,F,Outro'],
            'estado_civil' => ['nullable', 'string', 'max:50'],
            'profissao' => ['nullable', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:100'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:20'],
            'responsavel_legal_nome' => ['nullable', 'string', 'max:100'],
            'responsavel_legal_cpf' => ['nullable', 'string', 'max:18'],
            'responsavel_legal_parentesco' => ['nullable', 'string', 'max:50'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'possui_convenio' => ['boolean'],
            'convenio_nome' => ['nullable', 'string', 'max:100'],
            'convenio_plano' => ['nullable', 'string', 'max:100'],
            'convenio_carteirinha' => ['nullable', 'string', 'max:50'],
            'convenio_validade' => ['nullable', 'date'],
            'origem_paciente' => ['nullable', 'string', 'max:100'],
            'observacoes_administrativas' => ['nullable', 'string'],
            'consentimento_lgpd' => ['boolean'],
        ], [
            'nome.required' => 'O nome completo do paciente é obrigatório.',
            'cpf.unique' => 'Já existe um paciente cadastrado com este CPF.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $validated['possui_convenio'] = $request->boolean('possui_convenio');
        $validated['consentimento_lgpd'] = $request->boolean('consentimento_lgpd', true);
        if ($validated['consentimento_lgpd']) {
            $validated['consentimento_lgpd_em'] = now();
        }

        $paciente = Paciente::create($validated);

        AuditService::log('pacientes.create', "Cadastrou o paciente {$paciente->nome}", $paciente, null, $paciente->toArray());

        return redirect()->route('pacientes.show', $paciente->id)
            ->with('success', 'Paciente cadastrado com sucesso!');
    }

    public function show($id)
    {
        $paciente = Paciente::with([
            'agendamentos' => fn($q) => $q->with(['profissional', 'sala', 'servico'])->orderBy('data_hora_inicio', 'desc')->take(20),
            'pacotes' => fn($q) => $q->with(['servico', 'consumos'])->orderBy('created_at', 'desc'),
            'avaliacoes' => fn($q) => $q->with(['profissional'])->orderBy('data_avaliacao', 'desc'),
            'evolucoes' => fn($q) => $q->with(['profissional'])->orderBy('data_hora_evolucao', 'desc'),
            'documentos' => fn($q) => $q->with(['autor'])->orderBy('created_at', 'desc'),
        ])->findOrFail($id);

        return view('pacientes.show', compact('paciente'));
    }

    public function edit($id)
    {
        $paciente = Paciente::findOrFail($id);
        $convenios = Convenio::where('ativo', true)->orderBy('nome')->get();
        return view('pacientes.edit', compact('paciente', 'convenios'));
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:18', Rule::unique('pacientes', 'cpf')->ignore($paciente->id)],
            'rg' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:M,F,Outro'],
            'estado_civil' => ['nullable', 'string', 'max:50'],
            'profissao' => ['nullable', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:100'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:20'],
            'responsavel_legal_nome' => ['nullable', 'string', 'max:100'],
            'responsavel_legal_cpf' => ['nullable', 'string', 'max:18'],
            'responsavel_legal_parentesco' => ['nullable', 'string', 'max:50'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'possui_convenio' => ['boolean'],
            'convenio_nome' => ['nullable', 'string', 'max:100'],
            'convenio_plano' => ['nullable', 'string', 'max:100'],
            'convenio_carteirinha' => ['nullable', 'string', 'max:50'],
            'convenio_validade' => ['nullable', 'date'],
            'origem_paciente' => ['nullable', 'string', 'max:100'],
            'observacoes_administrativas' => ['nullable', 'string'],
            'status' => ['required', 'in:ativo,inativo,alta,transferido'],
        ]);

        $validated['possui_convenio'] = $request->boolean('possui_convenio');

        $old = $paciente->toArray();
        $paciente->update($validated);

        AuditService::log('pacientes.update', "Atualizou os dados do paciente {$paciente->nome}", $paciente, $old, $paciente->toArray());

        return redirect()->route('pacientes.show', $paciente->id)
            ->with('success', 'Dados do paciente atualizados com sucesso!');
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $nome = $paciente->nome;
        $paciente->delete();

        AuditService::log('pacientes.delete', "Exclusão lógica do paciente {$nome}", $paciente);

        return redirect()->route('pacientes.index')
            ->with('success', "Paciente {$nome} arquivado com sucesso.");
    }
}
