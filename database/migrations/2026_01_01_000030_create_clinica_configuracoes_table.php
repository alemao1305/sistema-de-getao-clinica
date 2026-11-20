<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinica_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia')->default('Clínica Fabris');
            $table->string('razao_social')->default('Clínica Fabris Fisioterapia e Reabilitação Ltda');
            $table->string('cnpj')->nullable();
            $table->string('crefito_pj')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->time('horario_abertura')->default('07:00:00');
            $table->time('horario_fechamento')->default('20:00:00');
            $table->integer('intervalo_agenda_minutos')->default(30);
            $table->integer('politica_cancelamento_horas')->default(24);
            $table->boolean('politica_falta_cobra_sessao')->default(true);
            $table->string('logo_path')->nullable();
            $table->text('cabecalho_documento')->nullable();
            $table->text('rodape_documento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinica_configuracoes');
    }
};
