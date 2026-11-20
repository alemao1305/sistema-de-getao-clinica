<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\ClinicaConfiguracao;

class ConfiguracaoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@clinicafabris.com.br')->first();
    }

    public function test_can_access_configuracoes_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('configuracoes.index'));

        $response->assertStatus(200);
        $response->assertSee('Configurações Gerais');
        $response->assertSee('Horários de Funcionamento');
        $response->assertSee('07:00');
        $response->assertSee('20:00');
    }

    public function test_can_update_configuracoes(): void
    {
        $response = $this->actingAs($this->admin)->put(route('configuracoes.update'), [
            'nome_fantasia' => 'Clínica Fabris Fisioterapia Avançada',
            'razao_social' => 'Clínica Fabris Fisioterapia e Reabilitação Ltda',
            'cnpj' => '12.345.678/0001-90',
            'crefito_pj' => '12345-SP/PJ',
            'responsavel_tecnico' => 'Dra. Fabris - CREFITO-3/123456-F',
            'horario_abertura' => '08:00',
            'horario_fechamento' => '19:30',
            'intervalo_agenda_minutos' => 45,
            'politica_cancelamento_horas' => 12,
            'politica_falta_cobra_sessao' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clinica_configuracoes', [
            'nome_fantasia' => 'Clínica Fabris Fisioterapia Avançada',
            'horario_abertura' => '08:00:00',
            'horario_fechamento' => '19:30:00',
            'intervalo_agenda_minutos' => 45,
            'politica_cancelamento_horas' => 12,
            'politica_falta_cobra_sessao' => 1,
        ]);
    }
}
