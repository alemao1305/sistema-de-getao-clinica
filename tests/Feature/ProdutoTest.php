<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Produto;
use App\Models\Paciente;
use App\Models\FormaPagamento;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Paciente $paciente;
    protected FormaPagamento $pix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@clinicafabris.com.br')->first();
        $this->paciente = Paciente::first() ?? Paciente::create([
            'nome' => 'Ana Clara Teste',
            'cpf' => '444.444.444-44',
            'telefone' => '(11) 94444-4444',
            'data_nascimento' => '1990-01-01',
            'genero' => 'feminino',
            'status' => 'ativo',
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
        ]);
        $this->pix = FormaPagamento::where('slug', 'pix')->first();
    }

    public function test_can_access_produtos_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('produtos.index'));

        $response->assertStatus(200);
        $response->assertSee('Produtos &amp; Estoque', false);
        $response->assertSee('Novo Produto');
        $response->assertSee('Faixa Elástica');
    }

    public function test_can_create_new_produto(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produtos.store'), [
            'nome' => 'Tornozeleira de Compressão',
            'categoria' => 'Ortese / Proteção',
            'unidade_medida' => 'PAR',
            'preco_custo' => '25.00',
            'preco_venda' => '59.90',
            'estoque_atual' => 12,
            'estoque_minimo' => 4,
            'localizacao' => 'Armário F',
            'descricao' => 'Tornozeleira elástica para reabilitação de entorse',
        ]);

        $response->assertRedirect(route('produtos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produtos', [
            'nome' => 'Tornozeleira de Compressão',
            'categoria' => 'Ortese / Proteção',
            'estoque_atual' => 12,
            'estoque_minimo' => 4,
        ]);

        // Verificando se a movimentação inicial de estoque foi lançada
        $this->assertDatabaseHas('produto_movimentacoes', [
            'tipo' => 'entrada',
            'quantidade' => 12,
            'motivo' => 'Saldo de estoque inicial no cadastro do produto',
        ]);
    }

    public function test_can_adjust_product_stock(): void
    {
        $produto = Produto::first();
        $estoqueOriginal = $produto->estoque_atual;

        // 1. Entrada de estoque
        $responseEntrada = $this->actingAs($this->admin)->post(route('produtos.ajustarEstoque', $produto->id), [
            'tipo' => 'entrada',
            'quantidade' => 10,
            'motivo' => 'Compra de lote com nota fiscal 1234',
        ]);

        $responseEntrada->assertRedirect();
        $this->assertEquals($estoqueOriginal + 10, $produto->fresh()->estoque_atual);

        // 2. Saída de estoque
        $responseSaida = $this->actingAs($this->admin)->post(route('produtos.ajustarEstoque', $produto->id), [
            'tipo' => 'saida',
            'quantidade' => 5,
            'motivo' => 'Consumo interno em atendimento',
        ]);

        $responseSaida->assertRedirect();
        $this->assertEquals($estoqueOriginal + 5, $produto->fresh()->estoque_atual);
    }

    public function test_cannot_make_negative_stock_exit(): void
    {
        $produto = Produto::first();
        $qtdExcedente = $produto->estoque_atual + 50;

        $response = $this->actingAs($this->admin)->post(route('produtos.ajustarEstoque', $produto->id), [
            'tipo' => 'saida',
            'quantidade' => $qtdExcedente,
            'motivo' => 'Tentativa de baixa além do saldo',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_can_toggle_product_status(): void
    {
        $produto = Produto::first();
        $statusOriginal = $produto->ativo;

        $response = $this->actingAs($this->admin)->patch(route('produtos.toggleStatus', $produto->id));

        $response->assertRedirect();
        $this->assertEquals(!$statusOriginal, $produto->fresh()->ativo);
    }

    public function test_sale_of_produto_deducts_stock_and_creates_audit_trail(): void
    {
        $produto = Produto::where('estoque_atual', '>=', 5)->first();
        $estoqueAntes = $produto->estoque_atual;

        $response = $this->actingAs($this->admin)->post(route('vendas.store'), [
            'paciente_id' => $this->paciente->id,
            'tipo_item' => 'produto',
            'item_id' => $produto->id,
            'data_venda' => now()->format('Y-m-d'),
            'valor_subtotal' => $produto->preco_venda,
            'valor_desconto' => 0,
            'pagamentos' => [
                [
                    'forma' => 'pix',
                    'valor' => $produto->preco_venda,
                    'parcelas' => 1,
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Confere dedução de 1 unidade do estoque
        $this->assertEquals($estoqueAntes - 1, $produto->fresh()->estoque_atual);

        // Confere que a movimentação do tipo 'venda' foi registrada no extrato
        $this->assertDatabaseHas('produto_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'venda',
            'quantidade' => 1,
        ]);
    }
}
