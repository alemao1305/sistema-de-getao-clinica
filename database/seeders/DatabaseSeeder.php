<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ClinicaConfiguracao;
use App\Models\Sala;
use App\Models\FormaPagamento;
use App\Models\CategoriaFinanceira;
use App\Models\Servico;
use App\Models\Pacote;
use App\Models\ModeloAvaliacao;
use App\Models\Convenio;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Perfis (Roles)
        $rolesData = [
            ['name' => 'Proprietária / Administradora', 'slug' => 'proprietaria', 'description' => 'Acesso irrestrito a todos os recursos e configurações da clínica.'],
            ['name' => 'Recepção', 'slug' => 'recepcao', 'description' => 'Gestão de pacientes, agenda, vendas e check-in operacional.'],
            ['name' => 'Fisioterapeuta', 'slug' => 'fisioterapeuta', 'description' => 'Agenda própria, prontuário clínico e evoluções de atendimentos.'],
            ['name' => 'Financeiro', 'slug' => 'financeiro', 'description' => 'Gestão de contas a pagar, receber, fluxo de caixa e relatórios financeiros.'],
            ['name' => 'Paciente', 'slug' => 'paciente', 'description' => 'Acesso restrito para consulta de próprios agendamentos e documentos liberados.'],
        ];

        $roles = [];
        foreach ($rolesData as $rd) {
            $roles[$rd['slug']] = Role::firstOrCreate(['slug' => $rd['slug']], $rd);
        }

        // 2. Permissões Granulares por Módulo
        $permissionsData = [
            // Pacientes
            ['name' => 'Visualizar Pacientes', 'slug' => 'pacientes.view', 'module' => 'pacientes'],
            ['name' => 'Cadastrar Pacientes', 'slug' => 'pacientes.create', 'module' => 'pacientes'],
            ['name' => 'Editar Pacientes', 'slug' => 'pacientes.edit', 'module' => 'pacientes'],
            ['name' => 'Excluir Pacientes', 'slug' => 'pacientes.delete', 'module' => 'pacientes'],
            
            // Agenda
            ['name' => 'Visualizar Agenda', 'slug' => 'agenda.view', 'module' => 'agenda'],
            ['name' => 'Criar Agendamento', 'slug' => 'agenda.create', 'module' => 'agenda'],
            ['name' => 'Remarcar/Editar Agendamento', 'slug' => 'agenda.edit', 'module' => 'agenda'],
            ['name' => 'Cancelar Agendamento', 'slug' => 'agenda.cancel', 'module' => 'agenda'],
            ['name' => 'Gerenciar Bloqueios', 'slug' => 'agenda.bloqueios', 'module' => 'agenda'],
            
            // Atendimentos & Prontuário Clínico (Acesso restrito)
            ['name' => 'Visualizar Prontuário', 'slug' => 'prontuario.view', 'module' => 'prontuario'],
            ['name' => 'Registrar Avaliação', 'slug' => 'prontuario.avaliacao', 'module' => 'prontuario'],
            ['name' => 'Registrar Evolução de Sessão', 'slug' => 'prontuario.evolucao', 'module' => 'prontuario'],
            ['name' => 'Retificar Prontuário (Aditivo)', 'slug' => 'prontuario.retificar', 'module' => 'prontuario'],
            ['name' => 'Anexar Documentos Clínicos', 'slug' => 'prontuario.anexos', 'module' => 'prontuario'],
            
            // Vendas & Pacotes
            ['name' => 'Visualizar Vendas', 'slug' => 'vendas.view', 'module' => 'vendas'],
            ['name' => 'Realizar Venda', 'slug' => 'vendas.create', 'module' => 'vendas'],
            ['name' => 'Consultar Saldo de Pacotes', 'slug' => 'pacotes.view', 'module' => 'vendas'],
            ['name' => 'Ajustar Saldo de Pacote', 'slug' => 'pacotes.manage', 'module' => 'vendas'],
            
            // Financeiro
            ['name' => 'Visualizar Financeiro', 'slug' => 'financeiro.view', 'module' => 'financeiro'],
            ['name' => 'Contas a Receber', 'slug' => 'financeiro.receber', 'module' => 'financeiro'],
            ['name' => 'Contas a Pagar', 'slug' => 'financeiro.pagar', 'module' => 'financeiro'],
            ['name' => 'Repasses a Profissionais', 'slug' => 'financeiro.repasses', 'module' => 'financeiro'],
            ['name' => 'Fluxo de Caixa', 'slug' => 'financeiro.fluxo', 'module' => 'financeiro'],
            
            // Relatórios
            ['name' => 'Acessar Relatórios', 'slug' => 'relatorios.view', 'module' => 'relatorios'],
            ['name' => 'Exportar Relatórios (PDF/CSV)', 'slug' => 'relatorios.export', 'module' => 'relatorios'],
            ['name' => 'Salvar Modelos de Relatório', 'slug' => 'relatorios.manage', 'module' => 'relatorios'],
            
            // Configurações e Usuários
            ['name' => 'Gerenciar Usuários e Permissões', 'slug' => 'usuarios.manage', 'module' => 'configuracoes'],
            ['name' => 'Configurações da Clínica', 'slug' => 'configuracoes.manage', 'module' => 'configuracoes'],
            ['name' => 'Trilha de Auditoria', 'slug' => 'auditoria.view', 'module' => 'configuracoes'],
            ['name' => 'Gerenciar Convênios', 'slug' => 'convenios.manage', 'module' => 'convenios'],
        ];

        $allPermIds = [];
        foreach ($permissionsData as $pd) {
            $p = Permission::firstOrCreate(['slug' => $pd['slug']], $pd);
            $allPermIds[$p->slug] = $p->id;
        }

        // Atribuir permissões padrão por Role
        // Proprietária: todas as permissões
        $roles['proprietaria']->permissions()->sync(array_values($allPermIds));

        // Recepção: pacientes, agenda, vendas e consulta básica de relatórios
        $roles['recepcao']->permissions()->sync([
            $allPermIds['pacientes.view'],
            $allPermIds['pacientes.create'],
            $allPermIds['pacientes.edit'],
            $allPermIds['agenda.view'],
            $allPermIds['agenda.create'],
            $allPermIds['agenda.edit'],
            $allPermIds['agenda.cancel'],
            $allPermIds['vendas.view'],
            $allPermIds['vendas.create'],
            $allPermIds['pacotes.view'],
            $allPermIds['prontuario.anexos'],
        ]);

        // Fisioterapeuta: agenda, prontuário clínico e evoluções
        $roles['fisioterapeuta']->permissions()->sync([
            $allPermIds['pacientes.view'],
            $allPermIds['agenda.view'],
            $allPermIds['agenda.create'],
            $allPermIds['agenda.edit'],
            $allPermIds['prontuario.view'],
            $allPermIds['prontuario.avaliacao'],
            $allPermIds['prontuario.evolucao'],
            $allPermIds['prontuario.retificar'],
            $allPermIds['prontuario.anexos'],
            $allPermIds['pacotes.view'],
        ]);

        // Financeiro: vendas, contas a pagar, contas a receber, repasses, fluxo e relatórios
        $roles['financeiro']->permissions()->sync([
            $allPermIds['vendas.view'],
            $allPermIds['vendas.create'],
            $allPermIds['financeiro.view'],
            $allPermIds['financeiro.receber'],
            $allPermIds['financeiro.pagar'],
            $allPermIds['financeiro.repasses'],
            $allPermIds['financeiro.fluxo'],
            $allPermIds['relatorios.view'],
            $allPermIds['relatorios.export'],
        ]);

        // 3. Usuários Operacionais Iniciais da Clínica
        // Usuário Administrador / Proprietária
        User::firstOrCreate(
            ['email' => 'admin@clinicafabris.com.br'],
            [
                'name' => 'Dra. Fabris (Proprietária)',
                'password' => Hash::make('admin123'),
                'role' => 'proprietaria',
                'phone' => '(11) 98765-4321',
                'cpf' => '111.222.333-44',
                'crefito' => 'CREFITO-3/123456-F',
                'especialidade' => 'Fisioterapia Traumato-Ortopédica e Gestão',
                'color' => '#0f766e',
                'is_active' => true,
            ]
        );

        // Usuário Recepção
        User::firstOrCreate(
            ['email' => 'recepcao@clinicafabris.com.br'],
            [
                'name' => 'Mariana Silva (Recepção)',
                'password' => Hash::make('recepcao123'),
                'role' => 'recepcao',
                'phone' => '(11) 97777-1111',
                'cpf' => '222.333.444-55',
                'color' => '#2563eb',
                'is_active' => true,
            ]
        );

        // Usuário Fisioterapeuta
        User::firstOrCreate(
            ['email' => 'fisioterapeuta@clinicafabris.com.br'],
            [
                'name' => 'Dra. Camila Ramos',
                'password' => Hash::make('fisio123'),
                'role' => 'fisioterapeuta',
                'phone' => '(11) 98888-2222',
                'cpf' => '333.444.555-66',
                'crefito' => 'CREFITO-3/184920-F',
                'especialidade' => 'Fisioterapia Traumato-Ortopédica e RPG',
                'color' => '#7c3aed',
                'is_active' => true,
            ]
        );

        // Usuário Financeiro
        User::firstOrCreate(
            ['email' => 'financeiro@clinicafabris.com.br'],
            [
                'name' => 'Carlos Alberto (Financeiro)',
                'password' => Hash::make('financeiro123'),
                'role' => 'financeiro',
                'phone' => '(11) 99999-3333',
                'cpf' => '444.555.666-77',
                'color' => '#059669',
                'is_active' => true,
            ]
        );

        // 4. Configurações Institucionais da Clínica
        ClinicaConfiguracao::instance();

        // 5. Salas de Atendimento
        $salas = [
            ['nome' => 'Sala 01 - Cinesioterapia & Avaliação', 'descricao' => 'Maca elétrica, espaldar, tablado e equipamentos de avaliação cinético-funcional.', 'cor' => '#0d9488', 'capacidade_simultanea' => 1],
            ['nome' => 'Sala 02 - Pilates Clínico', 'descricao' => 'Cadillac, Reformer, Wunda Chair e Barrel para reabilitação em Pilates.', 'cor' => '#2563eb', 'capacidade_simultanea' => 2],
            ['nome' => 'Sala 03 - Terapia Manual & RPG', 'descricao' => 'Maca para técnicas manuais miofasciais e postural RPG.', 'cor' => '#7c3aed', 'capacidade_simultanea' => 1],
            ['nome' => 'Sala 04 - Eletrotermofototerapia', 'descricao' => 'Ultrassom, TENS, FES, Laser e Turbilhão.', 'cor' => '#ea580c', 'capacidade_simultanea' => 2],
        ];

        foreach ($salas as $s) {
            Sala::firstOrCreate(['nome' => $s['nome']], $s);
        }

        // 6. Formas de Pagamento
        $formas = [
            ['nome' => 'Pix', 'slug' => 'pix', 'taxa_percentual' => 0.00, 'prazo_dias_compensacao' => 0],
            ['nome' => 'Dinheiro em Espécie', 'slug' => 'dinheiro', 'taxa_percentual' => 0.00, 'prazo_dias_compensacao' => 0],
            ['nome' => 'Cartão de Débito', 'slug' => 'cartao_debito', 'taxa_percentual' => 1.50, 'prazo_dias_compensacao' => 1],
            ['nome' => 'Cartão de Crédito', 'slug' => 'cartao_credito', 'taxa_percentual' => 2.99, 'prazo_dias_compensacao' => 30],
            ['nome' => 'Boleto Bancário', 'slug' => 'boleto', 'taxa_percentual' => 0.00, 'prazo_dias_compensacao' => 2],
            ['nome' => 'Convênio de Saúde', 'slug' => 'convenio', 'taxa_percentual' => 0.00, 'prazo_dias_compensacao' => 30],
        ];

        foreach ($formas as $f) {
            FormaPagamento::firstOrCreate(['slug' => $f['slug']], $f);
        }

        // 7. Categorias Financeiras
        $categorias = [
            ['nome' => 'Venda de Pacotes de Sessões', 'tipo' => 'receita', 'cor' => '#10b981'],
            ['nome' => 'Atendimentos e Consultas Avulsas', 'tipo' => 'receita', 'cor' => '#059669'],
            ['nome' => 'Faturamento de Convênios', 'tipo' => 'receita', 'cor' => '#3b82f6'],
            ['nome' => 'Mensalidades / Planos', 'tipo' => 'receita', 'cor' => '#6366f1'],
            ['nome' => 'Aluguel e Condomínio', 'tipo' => 'despesa', 'cor' => '#ef4444'],
            ['nome' => 'Energia Elétrica, Água e Internet', 'tipo' => 'despesa', 'cor' => '#f97316'],
            ['nome' => 'Materiais Fisioterapêuticos e Descartáveis', 'tipo' => 'despesa', 'cor' => '#eab308'],
            ['nome' => 'Manutenção de Equipamentos', 'tipo' => 'despesa', 'cor' => '#ec4899'],
            ['nome' => 'Repasses / Comissões de Fisioterapeutas', 'tipo' => 'despesa', 'cor' => '#8b5cf6'],
            ['nome' => 'Software, Impostos e Taxas Bancárias', 'tipo' => 'despesa', 'cor' => '#64748b'],
        ];

        foreach ($categorias as $c) {
            CategoriaFinanceira::firstOrCreate(['nome' => $c['nome']], $c);
        }

        // 8. Catálogo Padrão de Serviços
        $servicos = [
            [
                'nome' => 'Avaliação Fisioterapêutica Inicial',
                'descricao' => 'Anamnese completa, exame físico, goniometria, testes funcionais e elaboração do plano de tratamento.',
                'categoria' => 'Ortopedia',
                'duracao_minutos' => 60,
                'preco_padrao' => 180.00,
                'tipo' => 'avulso',
                'comissao_padrao_percentual' => 50.00,
            ],
            [
                'nome' => 'Sessão de Fisioterapia Traumato-Ortopédica',
                'descricao' => 'Cinesioterapia motora, eletroterapia, terapia manual e analgesia para lesões musculares e articulares.',
                'categoria' => 'Ortopedia',
                'duracao_minutos' => 50,
                'preco_padrao' => 130.00,
                'tipo' => 'avulso',
                'comissao_padrao_percentual' => 45.00,
            ],
            [
                'nome' => 'Sessão de RPG (Reeducação Postural Global)',
                'descricao' => 'Método postural individualizado para correção de escolioses, hérnias discais e desalinhamentos posturais.',
                'categoria' => 'Postura & Coluna',
                'duracao_minutos' => 60,
                'preco_padrao' => 160.00,
                'tipo' => 'avulso',
                'comissao_padrao_percentual' => 50.00,
            ],
            [
                'nome' => 'Sessão de Pilates Clínico Individual',
                'descricao' => 'Pilates orientado à reabilitação da coluna e fortalecimento do core com fisioterapeuta.',
                'categoria' => 'Pilates Clínico',
                'duracao_minutos' => 50,
                'preco_padrao' => 140.00,
                'tipo' => 'avulso',
                'comissao_padrao_percentual' => 45.00,
            ],
            [
                'nome' => 'Sessão de Fisioterapia Neurofuncional',
                'descricao' => 'Reabilitação neurológica (pós-AVE, Parkinson, esclerose, paralisias).',
                'categoria' => 'Neurologia',
                'duracao_minutos' => 60,
                'preco_padrao' => 150.00,
                'tipo' => 'avulso',
                'comissao_padrao_percentual' => 50.00,
            ],
        ];

        $servicoModels = [];
        foreach ($servicos as $s) {
            $servicoModels[$s['nome']] = Servico::firstOrCreate(['nome' => $s['nome']], $s);
        }

        // 9. Pacotes Comerciais Iniciais
        if (isset($servicoModels['Sessão de Fisioterapia Traumato-Ortopédica'])) {
            Pacote::firstOrCreate(
                ['nome' => 'Pacote 10 Sessões Fisioterapia Traumato-Ortopédica'],
                [
                    'servico_id' => $servicoModels['Sessão de Fisioterapia Traumato-Ortopédica']->id,
                    'descricao' => '10 sessões completas com desconto especial e validade estendida.',
                    'quantidade_sessoes' => 10,
                    'preco_total' => 1150.00, // De R$ 1300 por R$ 1150
                    'validade_dias' => 90,
                    'regras_utilizacao' => 'Validade de 90 dias a contar da data de compra. Cancelamentos devem ser feitos com no mínimo 24h de antecedência.',
                    'permite_parcelamento' => true,
                    'max_parcelas' => 3,
                ]
            );

            Pacote::firstOrCreate(
                ['nome' => 'Pacote 20 Sessões Fisioterapia Traumato-Ortopédica'],
                [
                    'servico_id' => $servicoModels['Sessão de Fisioterapia Traumato-Ortopédica']->id,
                    'descricao' => '20 sessões para reabilitação ortopédica e pós-operatória intensiva.',
                    'quantidade_sessoes' => 20,
                    'preco_total' => 2100.00,
                    'validade_dias' => 120,
                    'regras_utilizacao' => 'Validade de 120 dias.',
                    'permite_parcelamento' => true,
                    'max_parcelas' => 6,
                ]
            );
        }

        if (isset($servicoModels['Sessão de Pilates Clínico Individual'])) {
            Pacote::firstOrCreate(
                ['nome' => 'Pacote Mensal Pilates Clínico (8 Sessões - 2x/Semana)'],
                [
                    'servico_id' => $servicoModels['Sessão de Pilates Clínico Individual']->id,
                    'descricao' => '8 sessões mensais com acompanhamento individual.',
                    'quantidade_sessoes' => 8,
                    'preco_total' => 960.00,
                    'validade_dias' => 35,
                    'regras_utilizacao' => 'Consumo de 2 sessões por semana.',
                    'permite_parcelamento' => true,
                    'max_parcelas' => 2,
                ]
            );
        }

        // 10. Modelo de Avaliação Estruturado
        ModeloAvaliacao::firstOrCreate(
            ['nome' => 'Avaliação Traumato-Ortopédica Padrão'],
            [
                'especialidade' => 'Ortopedia & Traumatologia',
                'campos_estrutura' => [
                    'secoes' => [
                        'queixa_historico' => ['titulo' => 'Queixa Principal & Histórico', 'campos' => ['queixa_principal', 'hda', 'hpp', 'medicacoes']],
                        'dor' => ['titulo' => 'Escala Visual Analógica (EVA) e Características da Dor', 'campos' => ['escala_dor_eva', 'localizacao_dor', 'caracteristica_dor']],
                        'exame_fisico' => ['titulo' => 'Exame Físico e Funcional', 'campos' => ['inspecao_palpacao', 'amplitude_movimento', 'forca_muscular', 'testes_especiais_ortopedicos']],
                        'diagnostico_conduta' => ['titulo' => 'Diagnóstico Cinético-Funcional & Plano', 'campos' => ['diagnostico_fisioterapeutico', 'objetivos_terapeuticos', 'plano_tratamento']],
                    ]
                ],
                'ativo' => true,
            ]
        );

        // 11. Convênios
        $convenios = [
            ['nome' => 'Unimed', 'registro_ans' => '005711', 'prazo_pagamento_dias' => 30],
            ['nome' => 'Bradesco Saúde', 'registro_ans' => '005712', 'prazo_pagamento_dias' => 30],
            ['nome' => 'SulAmérica Saúde', 'registro_ans' => '005713', 'prazo_pagamento_dias' => 30],
            ['nome' => 'Amil', 'registro_ans' => '005714', 'prazo_pagamento_dias' => 30],
        ];

        foreach ($convenios as $conv) {
            Convenio::firstOrCreate(['nome' => $conv['nome']], $conv);
        }

        // 12. Produtos Fisioterapêuticos e Acessórios
        $produtos = [
            [
                'nome' => 'Faixa Elástica Theraband Nível Médio (Verde)',
                'codigo_barras' => '7891000100011',
                'sku' => 'TB-MED-VD',
                'categoria' => 'Cinesioterapia',
                'descricao' => 'Faixa de resistência látex para fortalecimento muscular e reabilitação.',
                'preco_custo' => 18.00,
                'preco_venda' => 35.00,
                'estoque_atual' => 25,
                'estoque_minimo' => 5,
                'unidade_medida' => 'UN',
            ],
            [
                'nome' => 'Faixa Elástica Theraband Nível Forte (Azul)',
                'codigo_barras' => '7891000100028',
                'sku' => 'TB-FOR-AZ',
                'categoria' => 'Cinesioterapia',
                'descricao' => 'Faixa elástica de alta resistência para exercícios de propriocepção e força.',
                'preco_custo' => 22.00,
                'preco_venda' => 42.00,
                'estoque_atual' => 18,
                'estoque_minimo' => 5,
                'unidade_medida' => 'UN',
            ],
            [
                'nome' => 'Kinesio Tape Bandagem Elástica 5cm x 5m (Bege)',
                'codigo_barras' => '7891000100035',
                'sku' => 'KT-BEGE-5M',
                'categoria' => 'Bandagens & Fitas',
                'descricao' => 'Fita neuromuscular respirável e hipoalergênica para suporte articular.',
                'preco_custo' => 20.00,
                'preco_venda' => 45.00,
                'estoque_atual' => 30,
                'estoque_minimo' => 8,
                'unidade_medida' => 'RL',
            ],
            [
                'nome' => 'Bolsa Térmica em Gel Quente / Frio Reutilizável',
                'codigo_barras' => '7891000100042',
                'sku' => 'BT-GEL-MED',
                'categoria' => 'Termoterapia & Crioterapia',
                'descricao' => 'Bolsa flexível para aplicação de calor ou gelo no alívio de dores agudas.',
                'preco_custo' => 12.00,
                'preco_venda' => 28.00,
                'estoque_atual' => 15,
                'estoque_minimo' => 4,
                'unidade_medida' => 'UN',
            ],
            [
                'nome' => 'Rolo de Liberação Miofascial Foam Roller 33cm',
                'codigo_barras' => '7891000100059',
                'sku' => 'RL-MIO-33',
                'categoria' => 'Liberação Miofascial',
                'descricao' => 'Rolo texturizado para alívio de nós de tensão muscular e relaxamento.',
                'preco_custo' => 45.00,
                'preco_venda' => 89.90,
                'estoque_atual' => 8,
                'estoque_minimo' => 3,
                'unidade_medida' => 'UN',
            ],
            [
                'nome' => 'Bola de Pilates Suíça 65cm com Bomba',
                'codigo_barras' => '7891000100066',
                'sku' => 'BL-PIL-65',
                'categoria' => 'Pilates & Reabilitação',
                'descricao' => 'Bola antiestouro para estabilização de tronco e fisioterapia motora.',
                'preco_custo' => 38.00,
                'preco_venda' => 79.00,
                'estoque_atual' => 10,
                'estoque_minimo' => 3,
                'unidade_medida' => 'UN',
            ],
        ];

        foreach ($produtos as $prod) {
            \App\Models\Produto::firstOrCreate(['nome' => $prod['nome']], $prod);
        }
    }
}
