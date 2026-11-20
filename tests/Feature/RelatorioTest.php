<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@clinicafabris.com.br')->first();
    }

    public function test_can_access_relatorios_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/relatorios');
        $response->assertStatus(200);
        $response->assertSee('Relatórios Configuráveis');
        $response->assertSee('Exportar CSV');
        $response->assertSee('Imprimir / PDF');
    }

    public function test_can_export_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('relatorios.csv', ['fonte' => 'agendamentos']));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public function test_can_view_printable_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('relatorios.pdf', ['fonte' => 'agendamentos']));
        $response->assertStatus(200);
        $response->assertSee('Relatório Analítico');
        $response->assertSee('Agendamentos');
    }

    public function test_can_save_report_model(): void
    {
        $response = $this->actingAs($this->admin)->post(route('relatorios.salvar'), [
            'nome' => 'Relatório Mensal de Ocupação',
            'fonte_dados' => 'agendamentos',
            'colunas_selecionadas' => ['data_hora', 'paciente', 'profissional', 'status'],
            'favorito' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('relatorio_modelos', [
            'nome' => 'Relatório Mensal de Ocupação',
            'user_id' => $this->admin->id,
            'fonte_dados' => 'agendamentos',
        ]);
    }
}
