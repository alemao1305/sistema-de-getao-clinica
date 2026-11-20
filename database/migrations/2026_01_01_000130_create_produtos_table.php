<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo_barras')->nullable()->index();
            $table->string('sku')->nullable()->index();
            $table->string('categoria')->default('Geral')->index();
            $table->text('descricao')->nullable();
            
            $table->decimal('preco_custo', 10, 2)->default(0.00);
            $table->decimal('preco_venda', 10, 2);
            
            $table->integer('estoque_atual')->default(0);
            $table->integer('estoque_minimo')->default(5);
            $table->string('unidade_medida', 10)->default('UN'); // UN, CX, RL, PAR, KIT
            
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('produto_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            
            $table->enum('tipo', ['entrada', 'saida', 'ajuste', 'venda']);
            $table->integer('quantidade');
            $table->integer('saldo_anterior');
            $table->integer('saldo_novo');
            $table->string('motivo');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_movimentacoes');
        Schema::dropIfExists('produtos');
    }
};
