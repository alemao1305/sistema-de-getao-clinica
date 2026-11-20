<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorio_modelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('fonte_dados'); // agendamentos, vendas, sessoes, financeiro_receitas, financeiro_despesas, repasses
            $table->json('colunas_selecionadas'); // Array de colunas que quer exibir
            $table->json('filtros_padrao')->nullable(); // Filtros salvos (período, profissional, status, etc.)
            $table->string('agrupamento')->nullable(); // Agrupar por profissional, status, categoria, etc.
            $table->string('ordenar_por')->nullable();
            $table->string('direcao_ordenacao', 4)->default('desc');
            $table->boolean('favorito')->default(false);
            $table->timestamps();
        });

        Schema::create('documentos_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Quem fez o upload
            $table->unsignedBigInteger('prontuario_id')->nullable()->index(); // Vinculado a avaliação/evolução
            
            $table->string('titulo');
            $table->string('tipo_documento'); // exame_imagem, laudo, atestado, consentimento_lgpd, encaminhamento, outro
            $table->string('arquivo_path');
            $table->string('nome_original');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanho_bytes')->nullable();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_anexos');
        Schema::dropIfExists('relatorio_modelos');
    }
};
