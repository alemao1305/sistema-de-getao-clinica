<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_financeiras', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('cor', 10)->default('#10b981');
            $table->timestamps();
        });

        Schema::create('formas_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Pix, Dinheiro, Cartão de Crédito, Cartão de Débito, Boleto, Convênio
            $table->string('slug')->unique();
            $table->decimal('taxa_percentual', 5, 2)->default(0.00); // Ex: taxa cartão
            $table->integer('prazo_dias_compensacao')->default(0); // D+0, D+1, D+30
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamento')->nullOnDelete();
            
            $table->string('descricao');
            $table->decimal('valor_nominal', 10, 2);
            $table->decimal('taxa_operadora', 10, 2)->default(0.00);
            $table->decimal('valor_recebido', 10, 2)->nullable();
            
            $table->date('data_vencimento')->index();
            $table->date('data_recebimento')->nullable()->index();
            
            $table->enum('status', ['pendente', 'pago', 'parcial', 'cancelado', 'estornado'])->default('pendente')->index();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            
            $table->string('descricao');
            $table->string('fornecedor')->nullable();
            $table->string('centro_custo')->nullable()->default('Geral');
            
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_pago', 10, 2)->nullable();
            
            $table->date('data_vencimento')->index();
            $table->date('data_pagamento')->nullable()->index();
            
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente')->index();
            $table->string('comprovante_path')->nullable();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('repasses_profissionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            
            $table->string('descricao');
            $table->decimal('valor_base_servico', 10, 2);
            $table->decimal('percentual_comissao', 5, 2)->default(0.00);
            $table->decimal('valor_fixo', 10, 2)->default(0.00);
            $table->decimal('valor_repasse', 10, 2);
            
            $table->date('data_competencia')->index();
            $table->date('data_pagamento')->nullable();
            
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente')->index();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repasses_profissionais');
        Schema::dropIfExists('contas_pagar');
        Schema::dropIfExists('contas_receber');
        Schema::dropIfExists('formas_pagamento');
        Schema::dropIfExists('categorias_financeiras');
    }
};
