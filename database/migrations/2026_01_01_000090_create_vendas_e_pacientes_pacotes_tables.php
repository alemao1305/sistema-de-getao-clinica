<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ex: VND-2026-0001
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Vendedor / Recepcionista
            $table->date('data_venda');
            
            $table->decimal('valor_subtotal', 10, 2);
            $table->decimal('valor_desconto', 10, 2)->default(0.00);
            $table->decimal('valor_total', 10, 2);
            
            $table->enum('status', ['pendente', 'pago', 'parcial', 'cancelado'])->default('pago');
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('venda_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->string('tipo_item'); // servico ou pacote
            $table->unsignedBigInteger('item_id'); // ID do servico ou pacote
            $table->string('descricao');
            $table->integer('quantidade')->default(1);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('venda_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->string('forma_pagamento'); // pix, dinheiro, cartao_credito, cartao_debito, boleto, transferencia, convenio
            $table->decimal('valor', 10, 2);
            $table->integer('parcelas')->default(1);
            $table->enum('status', ['pago', 'pendente', 'estornado'])->default('pago');
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('comprovante_autorizacao')->nullable();
            $table->timestamps();
        });

        Schema::create('paciente_pacotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            $table->foreignId('pacote_id')->nullable()->constrained('pacotes')->nullOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            
            $table->string('nome_pacote');
            $table->integer('sessoes_totais');
            $table->integer('sessoes_utilizadas')->default(0);
            $table->integer('sessoes_restantes');
            $table->integer('sessoes_bonus')->default(0);
            
            $table->date('data_inicio');
            $table->date('data_validade');
            
            $table->enum('status', ['ativo', 'concluido', 'expirado', 'pausado', 'cancelado'])->default('ativo');
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
        });

        Schema::create('paciente_pacote_consumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_pacote_id')->constrained('paciente_pacotes')->cascadeOnDelete();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('tipo', ['baixa_atendimento', 'estorno', 'ajuste_manual'])->default('baixa_atendimento');
            $table->integer('quantidade')->default(1); // 1 para baixa, -1 para estorno
            $table->string('motivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_pacote_consumos');
        Schema::dropIfExists('paciente_pacotes');
        Schema::dropIfExists('venda_pagamentos');
        Schema::dropIfExists('venda_itens');
        Schema::dropIfExists('vendas');
    }
};
