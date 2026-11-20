<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Unimed, Bradesco Saúde, Amil, SulAmérica
            $table->string('registro_ans')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->integer('prazo_pagamento_dias')->default(30);
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('convenio_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            
            $table->string('numero_guia')->index();
            $table->string('numero_carteirinha');
            $table->date('validade_guia');
            $table->integer('sessoes_autorizadas')->default(10);
            $table->integer('sessoes_executadas')->default(0);
            $table->decimal('valor_sessao_acordado', 10, 2);
            $table->decimal('valor_total_guia', 10, 2);
            
            $table->enum('status', [
                'autorizada',
                'em_execucao',
                'concluida',
                'faturada',
                'paga',
                'glosada'
            ])->default('autorizada')->index();
            
            $table->decimal('valor_glosado', 10, 2)->default(0.00);
            $table->text('motivo_glosa')->nullable();
            $table->date('data_faturamento')->nullable();
            $table->date('data_recebimento')->nullable();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_guias');
        Schema::dropIfExists('convenios');
    }
};
