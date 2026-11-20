<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'cpf',
        'crefito',
        'especialidade',
        'color',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'profissional_id');
    }

    public function prontuariosAvaliacoes(): HasMany
    {
        return $this->hasMany(ProntuarioAvaliacao::class, 'profissional_id');
    }

    public function prontuariosEvolucoes(): HasMany
    {
        return $this->hasMany(ProntuarioEvolucao::class, 'profissional_id');
    }

    public function repasses(): HasMany
    {
        return $this->hasMany(RepasseProfissional::class, 'profissional_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role checks
    public function isProprietaria(): bool
    {
        return $this->role === 'proprietaria';
    }

    public function isFisioterapeuta(): bool
    {
        return $this->role === 'fisioterapeuta';
    }

    public function isRecepcao(): bool
    {
        return $this->role === 'recepcao';
    }

    public function isFinanceiro(): bool
    {
        return $this->role === 'financeiro';
    }

    public function isPaciente(): bool
    {
        return $this->role === 'paciente';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'proprietaria' => 'Proprietária / Administradora',
            'recepcao' => 'Recepção',
            'fisioterapeuta' => 'Fisioterapeuta',
            'financeiro' => 'Financeiro',
            'paciente' => 'Paciente',
            default => ucfirst($this->role),
        };
    }

    /**
     * Verifica permissão granular por slug (ex: 'pacientes.create', 'prontuario.view')
     */
    public function hasPermissionTo(string $permissionSlug): bool
    {
        if ($this->isProprietaria()) {
            return true; // Administradora tem acesso irrestrito
        }

        // Verifica override individual se houver
        $custom = \DB::table('user_custom_permissions')
            ->join('permissions', 'permissions.id', '=', 'user_custom_permissions.permission_id')
            ->where('user_custom_permissions.user_id', $this->id)
            ->where('permissions.slug', $permissionSlug)
            ->value('user_custom_permissions.granted');

        if ($custom !== null) {
            return (bool) $custom;
        }

        // Verifica na role do usuário
        $role = Role::where('slug', $this->role)->first();
        if (!$role) {
            return false;
        }

        return $role->permissions()->where('slug', $permissionSlug)->exists();
    }
}
