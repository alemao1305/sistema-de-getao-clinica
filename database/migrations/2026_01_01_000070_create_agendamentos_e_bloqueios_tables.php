<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('users')->cascadeOnDelete(); // Fisioterapeuta responsável
            $table->foreignId('sala_id')->nullable()->constrained('salas')->nullOnDelete();
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->unsignedBigInteger('paciente_pacote_id')->nullable(); // Vinculado a pacote (definido na migration de pacotes de pacientes)
            
            $table->dateTime('data_hora_inicio')->index();
            $table->dateTime('data_hora_fim')->index();
            $table->integer('duracao_minutos')->default(50);
            
            $table->enum('status', [
                'agendado',
                'confirmado',
                'em_espera',
                'em_atendimento',
                'concluido',
                'falta',
                'cancelado'
            ])->default('agendado')->index();
            
            $table->enum('tipo_cobranca', ['avulso', 'pacote', 'convenio', 'retorno_gratuito'])->default('avulso');
            
            $table->text('motivo_cancelamento')->nullable();
            $table->text('observacoes')->nullable();
            
            // Recorrência
            $table->uuid('recorrencia_grupo_id')->nullable()->index();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bloqueios_agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sala_id')->nullable()->constrained('salas')->nullOnDelete();
            $table->string('titulo');
            $table->text('motivo')->nullable();
            $table->dateTime('data_hora_inicio');
            $table->dateTime('data_hora_fim');
            $table->enum('tipo', ['feriado', 'recesso', 'intervalo', 'reuniao', 'outro'])->default('intervalo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueios_agenda');
        Schema::dropIfExists('agendamentos');
    }
};
