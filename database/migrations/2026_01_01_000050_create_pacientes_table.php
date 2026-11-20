<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->index();
            $table->string('cpf', 14)->nullable()->unique();
            $table->string('rg', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->enum('sexo', ['M', 'F', 'Outro'])->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('profissao')->nullable();
            
            // Contatos
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contato_emergencia_nome')->nullable();
            $table->string('contato_emergencia_telefone', 20)->nullable();
            
            // Responsável legal (menores ou dependentes)
            $table->string('responsavel_legal_nome')->nullable();
            $table->string('responsavel_legal_cpf', 14)->nullable();
            $table->string('responsavel_legal_parentesco')->nullable();
            
            // Endereço
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            
            // Convênio / Plano
            $table->boolean('possui_convenio')->default(false);
            $table->string('convenio_nome')->nullable();
            $table->string('convenio_plano')->nullable();
            $table->string('convenio_carteirinha')->nullable();
            $table->date('convenio_validade')->nullable();
            
            // Administrativo e Origem
            $table->string('origem_paciente')->nullable(); // Ex: Indicação, Google, Instagram, Médico
            $table->text('observacoes_administrativas')->nullable();
            
            // LGPD
            $table->boolean('consentimento_lgpd')->default(true);
            $table->timestamp('consentimento_lgpd_em')->nullable();
            
            // Status
            $table->enum('status', ['ativo', 'inativo', 'alta', 'transferido'])->default('ativo');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
