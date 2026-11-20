<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Sala;
use App\Models\Servico;
use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use Carbon\Carbon;

class AgendaConflictTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $fisio;
    protected Paciente $paciente1;
    protected Paciente $paciente2;
    protected Sala $sala;
    protected Servico $servico;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@clinicafabris.com.br')->first();
        $this->fisio = User::where('email', 'fisioterapeuta@clinicafabris.com.br')->first();
        $this->sala = Sala::first();
        $this->servico = Servico::first();

        $this->paciente1 = Paciente::create([
            'nome' => 'Paciente Teste 1',
            'cpf' => '111.111.111-11',
            'telefone' => '(11) 91111-1111',
            'data_nascimento' => '1990-01-01',
            'genero' => 'outro',
            'status' => 'ativo',
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
        ]);

        $this->paciente2 = Paciente::create([
            'nome' => 'Paciente Teste 2',
            'cpf' => '222.222.222-22',
            'telefone' => '(11) 92222-2222',
            'data_nascimento' => '1992-02-02',
            'genero' => 'outro',
            'status' => 'ativo',
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
        ]);
    }

    public function test_scheduling_appointment_successfully(): void
    {
        $amanha = Carbon::tomorrow();

        $response = $this->actingAs($this->admin)->post('/agenda', [
            'paciente_id' => $this->paciente1->id,
            'profissional_id' => $this->fisio->id,
            'sala_id' => $this->sala->id,
            'servico_id' => $this->servico->id,
            'data' => $amanha->format('Y-m-d'),
            'horario' => '10:00',
            'duracao_minutos' => 50,
            'tipo_cobranca' => 'avulso',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('agendamentos', [
            'paciente_id' => $this->paciente1->id,
            'profissional_id' => $this->fisio->id,
            'status' => 'agendado',
        ]);
    }

    public function test_conflict_detection_for_same_professional(): void
    {
        $amanha = Carbon::tomorrow();
        $inicio = (clone $amanha)->setTime(14, 0);
        $fim = (clone $inicio)->addMinutes(50);

        // Agendamento 1 criado
        Agendamento::create([
            'paciente_id' => $this->paciente1->id,
            'profissional_id' => $this->fisio->id,
            'sala_id' => $this->sala->id,
            'servico_id' => $this->servico->id,
            'data_hora_inicio' => $inicio,
            'data_hora_fim' => $fim,
            'status' => 'agendado',
            'tipo_cobranca' => 'avulso',
            'valor_cobrado' => 150.00,
        ]);

        // Tentativa de Agendamento 2 sobrepondo o mesmo profissional (14:20 às 15:10)
        $response = $this->actingAs($this->admin)->post('/agenda', [
            'paciente_id' => $this->paciente2->id,
            'profissional_id' => $this->fisio->id,
            'sala_id' => $this->sala->id,
            'servico_id' => $this->servico->id,
            'data' => $amanha->format('Y-m-d'),
            'horario' => '14:20',
            'duracao_minutos' => 50,
            'tipo_cobranca' => 'avulso',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('conflito', strtolower(session('error')));
    }

    public function test_conflict_detection_for_schedule_block(): void
    {
        $amanha = Carbon::tomorrow();
        $inicioBloqueio = (clone $amanha)->setTime(8, 0);
        $fimBloqueio = (clone $amanha)->setTime(12, 0);

        // Criar bloqueio de agenda (reunião) para o profissional
        BloqueioAgenda::create([
            'profissional_id' => $this->fisio->id,
            'titulo' => 'Reunião Clínica Geral',
            'data_hora_inicio' => $inicioBloqueio,
            'data_hora_fim' => $fimBloqueio,
            'motivo' => 'reuniao',
        ]);

        // Tentar agendar no meio do bloqueio (09:30)
        $response = $this->actingAs($this->admin)->post('/agenda', [
            'paciente_id' => $this->paciente1->id,
            'profissional_id' => $this->fisio->id,
            'sala_id' => $this->sala->id,
            'servico_id' => $this->servico->id,
            'data' => $amanha->format('Y-m-d'),
            'horario' => '09:30',
            'duracao_minutos' => 50,
            'tipo_cobranca' => 'avulso',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('bloqueio', strtolower(session('error')));
    }
}
