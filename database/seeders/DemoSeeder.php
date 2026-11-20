<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Sala;
use App\Models\Servico;
use App\Models\Pacote;
use App\Models\Agendamento;
use App\Models\Venda;
use App\Models\PacientePacote;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\ProntuarioAvaliacao;
use App\Models\ProntuarioEvolucao;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $fisio = User::where('role', 'fisioterapeuta')->first() ?? User::first();
        $admin = User::where('role', 'proprietaria')->first() ?? User::first();
        $sala1 = Sala::first();
        $servicoOrto = Servico::where('nome', 'like', '%Ortopédica%')->first();
        $pacote10 = Pacote::where('quantidade_sessoes', 10)->first();

        // 1. Pacientes de Teste
        $p1 = Paciente::firstOrCreate(
            ['cpf' => '123.456.789-01'],
            [
                'nome' => 'Ana Beatriz Nogueira',
                'rg' => '45.123.456-7',
                'data_nascimento' => '1992-05-14',
                'sexo' => 'F',
                'estado_civil' => 'Solteira',
                'profissao' => 'Arquiteta',
                'telefone' => '(11) 98111-2233',
                'whatsapp' => '(11) 98111-2233',
                'email' => 'ana.nogueira@exemplo.com.br',
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'numero' => '1000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'origem_paciente' => 'Indicação Médica',
                'status' => 'ativo',
                'consentimento_lgpd' => true,
                'consentimento_lgpd_em' => now(),
            ]
        );

        $p2 = Paciente::firstOrCreate(
            ['cpf' => '234.567.890-12'],
            [
                'nome' => 'Roberto Ferreira Lima',
                'rg' => '32.987.654-1',
                'data_nascimento' => '1980-11-20',
                'sexo' => 'M',
                'estado_civil' => 'Casado',
                'profissao' => 'Engenheiro Civil',
                'telefone' => '(11) 97222-3344',
                'whatsapp' => '(11) 97222-3344',
                'email' => 'roberto.lima@exemplo.com.br',
                'cep' => '04538-132',
                'logradouro' => 'Rua Funchal',
                'numero' => '418',
                'bairro' => 'Vila Olímpia',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'origem_paciente' => 'Google',
                'status' => 'ativo',
                'consentimento_lgpd' => true,
                'consentimento_lgpd_em' => now(),
            ]
        );

        // 2. Venda de Pacote com Pagamento Dividido para Ana Beatriz
        if ($pacote10 && !Venda::where('paciente_id', $p1->id)->exists()) {
            $venda = Venda::create([
                'codigo' => Venda::gerarCodigo(),
                'paciente_id' => $p1->id,
                'user_id' => $admin->id,
                'data_venda' => now()->subDays(5)->format('Y-m-d'),
                'valor_subtotal' => 1150.00,
                'valor_desconto' => 50.00,
                'valor_total' => 1100.00,
                'status' => 'pago',
                'observacoes' => 'Desconto promocional de primeira avaliação',
            ]);

            $venda->itens()->create([
                'tipo_item' => 'pacote',
                'item_id' => $pacote10->id,
                'descricao' => $pacote10->nome,
                'quantidade' => 1,
                'valor_unitario' => 1100.00,
                'valor_total' => 1100.00,
            ]);

            // Split payment: R$ 500 Pix + R$ 600 Cartão de Crédito
            $venda->pagamentos()->create([
                'forma_pagamento' => 'pix',
                'valor' => 500.00,
                'parcelas' => 1,
                'status' => 'pago',
                'data_pagamento' => now()->subDays(5),
            ]);

            $venda->pagamentos()->create([
                'forma_pagamento' => 'cartao_credito',
                'valor' => 600.00,
                'parcelas' => 2,
                'status' => 'pago',
                'data_pagamento' => now()->subDays(5),
            ]);

            // Saldo do Pacote (10 sessões, 1 consumida)
            $pacotePaciente = PacientePacote::create([
                'paciente_id' => $p1->id,
                'venda_id' => $venda->id,
                'pacote_id' => $pacote10->id,
                'servico_id' => $servicoOrto->id,
                'nome_pacote' => $pacote10->nome,
                'sessoes_totais' => 10,
                'sessoes_utilizadas' => 1,
                'sessoes_restantes' => 9,
                'data_inicio' => now()->subDays(5),
                'data_validade' => now()->addDays(85),
                'status' => 'ativo',
            ]);

            $pacotePaciente->consumos()->create([
                'user_id' => $fisio->id,
                'tipo' => 'baixa_atendimento',
                'quantidade' => 1,
                'motivo' => 'Atendimento inicial concluído',
            ]);

            ContaReceber::create([
                'venda_id' => $venda->id,
                'paciente_id' => $p1->id,
                'descricao' => "Venda Pacote - {$venda->codigo}",
                'valor_nominal' => 1100.00,
                'valor_recebido' => 1100.00,
                'data_vencimento' => now()->subDays(5),
                'data_recebimento' => now()->subDays(5),
                'status' => 'pago',
            ]);
        }

        // 3. Agendamento para hoje
        $hoje = Carbon::today();
        Agendamento::firstOrCreate(
            [
                'paciente_id' => $p1->id,
                'data_hora_inicio' => $hoje->copy()->setTime(9, 0),
            ],
            [
                'profissional_id' => $fisio->id,
                'sala_id' => $sala1->id,
                'servico_id' => $servicoOrto->id,
                'data_hora_fim' => $hoje->copy()->setTime(9, 50),
                'duracao_minutos' => 50,
                'status' => 'confirmado',
                'tipo_cobranca' => 'pacote',
                'observacoes' => 'Paciente relatou melhora na dor lombar após a última sessão.',
            ]
        );

        Agendamento::firstOrCreate(
            [
                'paciente_id' => $p2->id,
                'data_hora_inicio' => $hoje->copy()->setTime(14, 0),
            ],
            [
                'profissional_id' => $fisio->id,
                'sala_id' => $sala1->id,
                'servico_id' => $servicoOrto->id,
                'data_hora_fim' => $hoje->copy()->setTime(14, 50),
                'duracao_minutos' => 50,
                'status' => 'agendado',
                'tipo_cobranca' => 'avulso',
                'observacoes' => 'Avaliação inicial para dor no ombro direito.',
            ]
        );

        // 4. Prontuário para Ana Beatriz
        if (!ProntuarioAvaliacao::where('paciente_id', $p1->id)->exists()) {
            ProntuarioAvaliacao::create([
                'paciente_id' => $p1->id,
                'profissional_id' => $fisio->id,
                'titulo' => 'Avaliação Traumato-Ortopédica da Coluna Lombar',
                'data_avaliacao' => now()->subDays(5),
                'queixa_principal' => 'Dor lombar baixa irradiada para glúteo esquerdo há 3 meses.',
                'historia_doenca_atual' => 'Início insidioso após período prolongado em home office sem cadeira ergonômica.',
                'historia_patologica_pregressa' => 'Nega cirurgias ou traumas prévios. Sedentária.',
                'escala_dor_eva' => 7,
                'localizacao_dor' => 'Região L4-L5 e sacroilíaca esquerda.',
                'caracteristica_dor' => 'Pontada e peso constante, piora ao sentar.',
                'inspecao_palpacao' => 'Hipertonia dos paravertebrais lombares e quadrado lombar bilateral.',
                'amplitude_movimento' => 'Flexão de tronco limitada a 45 graus com dor (distância dedo-chão 35cm).',
                'forca_muscular' => 'Grau 4 para glúteo médio e extensores de tronco.',
                'testes_especiais_ortopedicos' => 'Teste de Lasègue positivo a 50 graus à esquerda. Slump test positivo.',
                'diagnostico_fisioterapeutico' => 'Disfunção cinético-funcional lombar com radiculopatia L5 e restrição miofascial.',
                'objetivos_terapeuticos' => 'Analgesia e modulação da dor, liberação miofascial, fortalecimento do core e reeducação postural.',
                'plano_tratamento' => 'Cinesioterapia motora 2 vezes por semana, mobilização neural e terapia manual.',
                'frequencia_semanal_sugerida' => '2 vezes por semana',
                'finalizado' => true,
                'finalizado_em' => now()->subDays(5),
                'assinatura_profissional' => "{$fisio->name} - {$fisio->crefito}",
            ]);

            ProntuarioEvolucao::create([
                'paciente_id' => $p1->id,
                'profissional_id' => $fisio->id,
                'data_hora_evolucao' => now()->subDays(3)->setTime(10, 0),
                'numero_sessao' => 1,
                'escala_dor_inicial' => 7,
                'relato_subjetivo' => 'Paciente refere melhora leve da dor após início do repouso ativo.',
                'procedimentos_realizados' => 'Terapia manual lombar, mobilização neural do nervo ciático e ativação do transverso do abdome.',
                'resposta_terapeutica' => 'Boa tolerância às manobras, relatou sensação de alívio e leveza.',
                'escala_dor_final' => 4,
                'orientacoes_domiciliares' => 'Evitar permanência prolongada sentada; aplicar compressa morna por 20min à noite.',
                'finalizado' => true,
                'finalizado_em' => now()->subDays(3)->setTime(10, 50),
                'assinatura_profissional' => "{$fisio->name} - {$fisio->crefito}",
            ]);
        }

        // 5. Conta a Pagar de Teste
        if (!ContaPagar::where('descricao', 'like', '%Aluguel%')->exists()) {
            ContaPagar::create([
                'descricao' => 'Aluguel do Imóvel da Clínica',
                'fornecedor' => 'Imobiliária Central',
                'centro_custo' => 'Infraestrutura',
                'valor' => 4500.00,
                'data_vencimento' => now()->addDays(10),
                'status' => 'pendente',
            ]);
        }
    }
}
