<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuditService;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();
        $roles = Role::all();
        return view('configuracoes.usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:proprietaria,recepcao,fisioterapeuta,financeiro,paciente'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:18', 'unique:users,cpf'],
            'crefito' => ['nullable', 'string', 'max:50'],
            'especialidade' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:10'],
        ], [
            'email.unique' => 'Este e-mail já está sendo utilizado.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);
        AuditService::log('usuarios.create', "Cadastrou o usuário {$user->name} com perfil '{$user->role}'", $user);

        return back()->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:proprietaria,recepcao,fisioterapeuta,financeiro,paciente'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:18', Rule::unique('users', 'cpf')->ignore($user->id)],
            'crefito' => ['nullable', 'string', 'max:50'],
            'especialidade' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
            'new_password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (!empty($validated['new_password'])) {
            $validated['password'] = Hash::make($validated['new_password']);
        }
        unset($validated['new_password']);

        $user->update($validated);
        AuditService::log('usuarios.update', "Atualizou o usuário {$user->name}", $user);

        return back()->with('success', 'Dados do usuário atualizados!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode desativar seu próprio usuário logado.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'ativado' : 'desativado';
        AuditService::log('usuarios.status', "Usuário {$user->name} foi {$statusStr}", $user);

        return back()->with('success', "Acesso do usuário {$user->name} foi {$statusStr}.");
    }

    /**
     * Matriz de permissões por perfil e usuário
     */
    public function permissoes()
    {
        $roles = Role::with('permissions')->get();
        $permissionsByModule = Permission::all()->groupBy('module');
        return view('configuracoes.usuarios.permissoes', compact('roles', 'permissionsByModule'));
    }

    public function salvarPermissoesRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = $request->input('permissions', []);

        $role->permissions()->sync($permissions);
        AuditService::log('usuarios.permissoes', "Atualizou a matriz de permissões do perfil '{$role->name}'");

        return back()->with('success', "Permissões do perfil {$role->name} atualizadas com sucesso!");
    }
}
