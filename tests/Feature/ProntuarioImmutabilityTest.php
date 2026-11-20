<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Paciente;
use App\Models\ProntuarioAvaliacao;
use App\Models\ProntuarioEvolucao;
use App\Models\ProntuarioAditivo;
use App\Models\AuditLog;

class ProntuarioImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $fisio;
    protected Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->fisio = User::where('email', 'fisioterapeuta@clinicafabris.com.br')->first();

        $this->paciente = Paciente::create([
            'nome' => 'Luciana Albuquerque',
            'cpf' => '444.444.444-44',
            'telefone' => '(11) 94444-4444',
            'data_nascimento' => '1995-10-20',
            'genero' => 'feminino',
            'status' => 'ativo',
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
        ]);
    }

    public function test_physiotherapist_can_record_evaluation(): void
    {
        $response = $this->actingAs($this->fisio)->post('/prontuarios/avaliacao', [
            'paciente_id' => $this->paciente->id,
            'titulo' => 'Avaliação Traumato-Ortopédica da Coluna Lombar',
            'data_avaliacao' => now()->format('Y-m-d'),
            'queixa_principal' => 'Dor lombar intensa irradiando para MID há 3 semanas.',
            'historia_doenca_atual' => 'Início após esforço físico excessivo no trabalho.',
            'escala_dor_eva' => 8,
            'localizacao_dor' => 'Região lombar L4-S1',
            'caracteristica_dor' => 'Queimação',
            'inspecao_palpacao' => 'Tensão muscular paravertebral severa.',
            'amplitude_movimento' => 'Flexão lombar limitada a 40 graus com dor.',
            'forca_muscular' => 'Grau 4 para flexores de quadril.',
            'testes_especiais_ortopedicos' => 'Teste de Lasègue positivo a 45 graus à direita.',
            'diagnostico_fisioterapeutico' => 'Lombociatalgia mecânico-postural com irritação radicular.',
            'objetivos_terapeuticos' => 'Redução de quadro álgico, ganho de ADM e estabilização de core.',
            'plano_tratamento' => 'Terapia manual, analgesia eletroterápica e cinesioterapia progressiva.',
            'frequencia_semanal_sugerida' => '3x por semana',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prontuario_avaliacoes', [
            'paciente_id' => $this->paciente->id,
            'profissional_id' => $this->fisio->id,
            'escala_dor_eva' => 8,
            'finalizado' => true,
        ]);

        // Verificar registro de auditoria
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'prontuario.avaliacao.create',
            'user_id' => $this->fisio->id,
        ]);
    }

    public function test_physiotherapist_can_record_soap_evolution(): void
    {
        $response = $this->actingAs($this->fisio)->post('/prontuarios/evolucao', [
            'paciente_id' => $this->paciente->id,
            'data_hora_evolucao' => now()->format('Y-m-d H:i'),
            'escala_dor_inicial' => 7,
            'relato_subjetivo' => 'Paciente relata melhora de 30% da dor após repouso no final de semana.',
            'procedimentos_realizados' => 'Realizada eletroterapia TENS 20min + pompagem cervical e lombar.',
            'resposta_terapeutica' => 'Boa tolerância ao protocolo, sem queixas de desconforto agudo.',
            'escala_dor_final' => 4,
            'orientacoes_domiciliares' => 'Compressa morna por 20 minutos à noite.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prontuario_evolucoes', [
            'paciente_id' => $this->paciente->id,
            'profissional_id' => $this->fisio->id,
            'numero_sessao' => 1,
            'escala_dor_inicial' => 7,
            'escala_dor_final' => 4,
            'finalizado' => true,
        ]);
    }

    public function test_addendum_records_clarification_with_mandatory_justification(): void
    {
        $evolucao = ProntuarioEvolucao::create([
            'paciente_id' => $this->paciente->id,
            'profissional_id' => $this->fisio->id,
            'numero_sessao' => 2,
            'data_hora_evolucao' => now(),
            'relato_subjetivo' => 'Paciente relatou ausência de dor matinal.',
            'procedimentos_realizados' => 'Cinesioterapia ativa com bola suíça.',
            'resposta_terapeutica' => 'Estabilidade lombopélvica preservada.',
            'escala_dor_inicial' => 3,
            'escala_dor_final' => 1,
            'finalizado' => true,
        ]);

        // Tentar enviar aditivo sem justificativa (deve falhar na validação)
        $resSemJustificativa = $this->actingAs($this->fisio)->post('/prontuarios/aditivo', [
            'registro_tipo' => 'evolucao',
            'registro_id' => $evolucao->id,
            'conteudo_aditivo' => 'Correção de dados clínicos da sessão',
            'justificativa' => '',
        ]);
        $resSemJustificativa->assertSessionHasErrors(['justificativa']);

        // Enviar aditivo válido
        $response = $this->actingAs($this->fisio)->post('/prontuarios/aditivo', [
            'registro_tipo' => 'evolucao',
            'registro_id' => $evolucao->id,
            'conteudo_aditivo' => 'Nota complementar: paciente informou que fez uso de relaxante muscular antes da sessão.',
            'justificativa' => 'Informação clínica relevante obtida ao término do atendimento.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('prontuario_aditivos', [
            'registro_tipo' => 'evolucao',
            'registro_id' => $evolucao->id,
            'user_id' => $this->fisio->id,
        ]);

        // A evolução original permaneceu intacta (imutabilidade clínica)
        $evolucao->refresh();
        $this->assertEquals('Paciente relatou ausência de dor matinal.', $evolucao->relato_subjetivo);

        // Registro de auditoria gerado
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'prontuario.aditivo.create',
            'user_id' => $this->fisio->id,
        ]);
    }
}
