<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('categoria')->default('Fisioterapia'); // Ortopedia, Neurologia, RPG, Pilates, etc.
            $table->integer('duracao_minutos')->default(50);
            $table->decimal('preco_padrao', 10, 2)->default(0.00);
            $table->enum('tipo', ['avulso', 'pacote', 'plano', 'convenio'])->default('avulso');
            $table->decimal('comissao_padrao_percentual', 5, 2)->nullable()->default(0.00);
            $table->decimal('comissao_padrao_fixo', 10, 2)->nullable()->default(0.00);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('pacotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('quantidade_sessoes')->default(10);
            $table->decimal('preco_total', 10, 2);
            $table->integer('validade_dias')->default(90); // 90 dias padrão
            $table->text('regras_utilizacao')->nullable();
            $table->boolean('permite_parcelamento')->default(true);
            $table->integer('max_parcelas')->default(3);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacotes');
        Schema::dropIfExists('servicos');
    }
};
