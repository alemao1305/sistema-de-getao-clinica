<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelos_avaliacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Avaliação Traumato-Ortopédica, Neurofuncional, Pilates
            $table->string('especialidade')->default('Fisioterapia Geral');
            $table->json('campos_estrutura')->nullable(); // Configuração dinâmica de seções/campos
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('prontuario_avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            $table->foreignId('modelo_avaliacao_id')->nullable()->constrained('modelos_avaliacao')->nullOnDelete();
            
            $table->string('titulo')->default('Avaliação Fisioterapêutica');
            $table->date('data_avaliacao');
            
            // Queixa e Histórico
            $table->text('queixa_principal');
            $table->text('historia_doenca_atual')->nullable(); // HDA
            $table->text('historia_patologica_pregressa')->nullable(); // HPP (cirurgias, traumas, comorbidades)
            $table->text('medicacoes_em_uso')->nullable();
            
            // Avaliação da Dor
            $table->integer('escala_dor_eva')->default(0); // 0 a 10
            $table->string('localizacao_dor')->nullable();
            $table->string('caracteristica_dor')->nullable(); // Queimação, pontada, peso, latejante
            $table->string('fatores_melhora')->nullable();
            $table->string('fatores_piora')->nullable();
            
            // Exame Físico e Funcional
            $table->text('inspecao_palpacao')->nullable();
            $table->text('amplitude_movimento')->nullable(); // ADM / Goniometria
            $table->text('forca_muscular')->nullable();
            $table->text('testes_especiais_ortopedicos')->nullable();
            $table->text('avaliacao_postural')->nullable();
            
            // Diagnóstico Fisioterapêutico Cinético-Funcional
            $table->text('diagnostico_fisioterapeutico');
            
            // Objetivos e Condutas
            $table->text('objetivos_terapeuticos');
            $table->text('plano_tratamento');
            $table->string('frequencia_semanal_sugerida')->nullable();
            
            // Assinatura e Integridade
            $table->boolean('finalizado')->default(false);
            $table->timestamp('finalizado_em')->nullable();
            $table->string('assinatura_profissional')->nullable(); // Nome e CREFITO no momento da finalização
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prontuario_evolucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            
            $table->dateTime('data_hora_evolucao');
            $table->integer('numero_sessao')->nullable();
            
            // Registro Clínico Estruturado
            $table->integer('escala_dor_inicial')->nullable(); // EVA inicial
            $table->text('relato_subjetivo'); // Como o paciente se apresentou
            $table->text('procedimentos_realizados'); // Conduta, exercícios, aparelhos
            $table->text('resposta_terapeutica')->nullable(); // Reação e nível de dor pós-sessão
            $table->integer('escala_dor_final')->nullable(); // EVA final
            $table->text('orientacoes_domiciliares')->nullable();
            
            // Assinatura e Integridade
            $table->boolean('finalizado')->default(true);
            $table->timestamp('finalizado_em')->nullable();
            $table->string('assinatura_profissional')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Aditivos / Retificações justificadas (LGPD & Integridade Médica)
        Schema::create('prontuario_aditivos', function (Blueprint $table) {
            $table->id();
            $table->string('registro_tipo'); // avaliacao ou evolucao
            $table->unsignedBigInteger('registro_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('justificativa');
            $table->text('conteudo_aditivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prontuario_aditivos');
        Schema::dropIfExists('prontuario_evolucoes');
        Schema::dropIfExists('prontuario_avaliacoes');
        Schema::dropIfExists('modelos_avaliacao');
    }
};
