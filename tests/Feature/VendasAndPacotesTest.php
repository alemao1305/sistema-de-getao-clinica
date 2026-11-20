<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Pacote;
use App\Models\Servico;
use App\Models\FormaPagamento;
use App\Models\PacientePacote;
use App\Models\Agendamento;
use App\Models\Sala;
use App\Models\RepasseProfissional;

class VendasAndPacotesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $fisio;
    protected Paciente $paciente;
    protected Pacote $pacote;
    protected FormaPagamento $pix;
    protected FormaPagamento $cartao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@clinicafabris.com.br')->first();
        $this->fisio = User::where('email', 'fisioterapeuta@clinicafabris.com.br')->first();
        $this->pacote = Pacote::first();
        $this->pix = FormaPagamento::where('slug', 'pix')->first();
        $this->cartao = FormaPagamento::where('slug', 'cartao_credito')->first();

        $this->paciente = Paciente::create([
            'nome' => 'Marcos Vinicius',
            'cpf' => '333.333.333-33',
            'telefone' => '(11) 93333-3333',
            'data_nascimento' => '1985-05-15',
            'genero' => 'masculino',
            'status' => 'ativo',
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
        ]);
    }

    public function test_sale_with_split_payments_creates_package_balance(): void
    {
        $valorTotal = $this->pacote->preco_total;
        $metade = round($valorTotal / 2, 2);
        $resto = round($valorTotal - $metade, 2);

        $response = $this->actingAs($this->admin)->post('/vendas', [
            'paciente_id' => $this->paciente->id,
            'tipo_item' => 'pacote',
            'item_id' => $this->pacote->id,
            'valor_subtotal' => $valorTotal,
            'valor_desconto' => 0,
            'sessoes_bonus' => 0,
            'data_venda' => now()->format('Y-m-d'),
            'pagamentos' => [
                [
                    'forma' => 'pix',
                    'valor' => $metade,
                    'parcelas' => 1,
                ],
                [
                    'forma' => 'cartao_credito',
                    'valor' => $resto,
                    'parcelas' => 3,
                ]
            ],
            'observacoes' => 'Venda com divisão Pix + Cartão de Crédito',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar Venda
        $this->assertDatabaseHas('vendas', [
            'paciente_id' => $this->paciente->id,
            'valor_total' => $valorTotal,
            'status' => 'pago',
        ]);

        // Verificar Pagamentos (2 formas)
        $this->assertDatabaseHas('venda_pagamentos', [
            'forma_pagamento' => 'pix',
            'valor' => $metade,
        ]);
        $this->assertDatabaseHas('venda_pagamentos', [
            'forma_pagamento' => 'cartao_credito',
            'valor' => $resto,
            'parcelas' => 3,
        ]);

        // Verificar saldo do pacote gerado para o paciente
        $this->assertDatabaseHas('paciente_pacotes', [
            'paciente_id' => $this->paciente->id,
            'pacote_id' => $this->pacote->id,
            'sessoes_totais' => $this->pacote->quantidade_sessoes,
            'sessoes_restantes' => $this->pacote->quantidade_sessoes,
            'sessoes_utilizadas' => 0,
            'status' => 'ativo',
        ]);
    }

    public function test_consuming_session_decrements_balance_and_creates_commission_repasse(): void
    {
        // 1. Criar saldo de pacote para o paciente
        $pacientePacote = PacientePacote::create([
            'paciente_id' => $this->paciente->id,
            'pacote_id' => $this->pacote->id,
            'servico_id' => $this->pacote->servico_id,
            'nome_pacote' => $this->pacote->nome,
            'sessoes_totais' => 10,
            'sessoes_utilizadas' => 0,
            'sessoes_restantes' => 10,
            'sessoes_bonus' => 0,
            'data_inicio' => now(),
            'data_validade' => now()->addDays(90),
            'status' => 'ativo',
        ]);

        $sala = Sala::first();
        $servico = Servico::find($this->pacote->servico_id);

        // 2. Criar agendamento vinculado ao pacote
        $agendamento = Agendamento::create([
            'paciente_id' => $this->paciente->id,
            'profissional_id' => $this->fisio->id,
            'sala_id' => $sala->id,
            'servico_id' => $servico->id,
            'paciente_pacote_id' => $pacientePacote->id,
            'data_hora_inicio' => now()->subHour(),
            'data_hora_fim' => now()->subMinutes(10),
            'status' => 'agendado',
            'tipo_cobranca' => 'pacote',
            'valor_cobrado' => 0.00,
        ]);

        // 3. Concluir atendimento com débito da sessão
        $response = $this->actingAs($this->admin)->patch("/agenda/{$agendamento->id}/status", [
            'status' => 'concluido',
        ]);

        $response->assertSessionHas('success');

        // 4. Verificar se o saldo do pacote foi decrementado
        $pacientePacote->refresh();
        $this->assertEquals(9, $pacientePacote->sessoes_restantes);
        $this->assertEquals(1, $pacientePacote->sessoes_utilizadas);

        // 5. Verificar consumo gravado no extrato
        $this->assertDatabaseHas('paciente_pacote_consumos', [
            'paciente_pacote_id' => $pacientePacote->id,
            'agendamento_id' => $agendamento->id,
            'tipo' => 'baixa_atendimento',
        ]);

        // 6. Verificar repasse do fisioterapeuta gerado
        $this->assertDatabaseHas('repasses_profissionais', [
            'profissional_id' => $this->fisio->id,
            'agendamento_id' => $agendamento->id,
            'status' => 'pendente',
        ]);
    }
}
