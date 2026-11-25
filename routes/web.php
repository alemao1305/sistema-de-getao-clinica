<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ProntuarioController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PacientePacoteController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\PacoteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CepController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas / Autenticação
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Exigem Usuário Autenticado e Ativo)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Pacientes
    Route::prefix('pacientes')->name('pacientes.')->group(function () {
        Route::get('/', [PacienteController::class, 'index'])->name('index');
        Route::get('/create', [PacienteController::class, 'create'])->name('create');
        Route::post('/', [PacienteController::class, 'store'])->name('store');
        Route::get('/{id}', [PacienteController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PacienteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PacienteController::class, 'update'])->name('update');
        Route::delete('/{id}', [PacienteController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [PacienteController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 3. Agenda & Atendimentos
    Route::prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', [AgendaController::class, 'index'])->name('index');
        Route::post('/', [AgendaController::class, 'store'])->name('store');
        Route::match(['post', 'patch'], '/{id}/status', [AgendaController::class, 'updateStatus'])->name('status');
        Route::match(['post', 'patch'], '/{id}/update-status', [AgendaController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/remarcar', [AgendaController::class, 'remarcar'])->name('remarcar');
        Route::get('/{id}/atendimento', [AgendaController::class, 'atendimento'])->name('atendimento');
        Route::post('/bloqueios', [AgendaController::class, 'storeBloqueio'])->name('bloqueios.store');
        Route::delete('/bloqueios/{id}', [AgendaController::class, 'destroyBloqueio'])->name('bloqueios.destroy');
        Route::get('/salas', [SalaController::class, 'index'])->name('salas');
        Route::get('/export/ical', [AgendaController::class, 'exportIcal'])->name('export.ical');
        Route::get('/ical', [AgendaController::class, 'exportIcal'])->name('ical');
    });

    // 4. Prontuários & Evolução Fisioterapêutica
    Route::prefix('prontuarios')->name('prontuarios.')->group(function () {
        Route::get('/', [ProntuarioController::class, 'index'])->name('index');
        Route::get('/paciente/{pacienteId}', [ProntuarioController::class, 'show'])->name('show');
        Route::get('/paciente/{pacienteId}/avaliacao/nova', [ProntuarioController::class, 'createAvaliacao'])->name('avaliacoes.create');
        Route::post('/avaliacao', [ProntuarioController::class, 'storeAvaliacao'])->name('avaliacoes.store');
        Route::post('/evolucao', [ProntuarioController::class, 'storeEvolucao'])->name('evolucoes.store');
        Route::post('/aditivo', [ProntuarioController::class, 'storeAditivo'])->name('aditivos.store');
        Route::get('/paciente/{pacienteId}/declaracao', [ProntuarioController::class, 'emitirDeclaracao'])->name('declaracao');
    });

    // 5. Comercial: Vendas & Pacotes
    Route::prefix('vendas')->name('vendas.')->group(function () {
        Route::get('/', [VendaController::class, 'index'])->name('index');
        Route::get('/nova', [VendaController::class, 'create'])->name('create');
        Route::post('/', [VendaController::class, 'store'])->name('store');
        Route::get('/{id}', [VendaController::class, 'show'])->name('show');
    });

    Route::prefix('pacientes-pacotes')->name('pacientes-pacotes.')->group(function () {
        Route::get('/', [PacientePacoteController::class, 'index'])->name('index');
        Route::get('/{id}', [PacientePacoteController::class, 'show'])->name('show');
        Route::post('/{id}/ajustar', [PacientePacoteController::class, 'ajustarSaldo'])->name('ajustar');
        Route::post('/{id}/ajustar-saldo', [PacientePacoteController::class, 'ajustarSaldo'])->name('ajustar-saldo');
    });

    // Catálogo de Produtos Fisioterapêuticos & Controle de Estoque
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/', [ProdutoController::class, 'index'])->name('index');
        Route::post('/', [ProdutoController::class, 'store'])->name('store');
        Route::match(['post', 'put'], '/{id}', [ProdutoController::class, 'update'])->name('update');
        Route::post('/{id}/ajustar-estoque', [ProdutoController::class, 'ajustarEstoque'])->name('ajustar-estoque');
        Route::post('/{id}/ajustarEstoque', [ProdutoController::class, 'ajustarEstoque'])->name('ajustarEstoque');
        Route::match(['post', 'patch'], '/{id}/toggle-status', [ProdutoController::class, 'toggleStatus'])->name('toggle-status');
        Route::match(['post', 'patch'], '/{id}/toggleStatus', [ProdutoController::class, 'toggleStatus'])->name('toggleStatus');
    });

    // 6. Financeiro (Contas a Receber, Contas a Pagar, Fluxo de Caixa, Repasses)
    Route::prefix('financeiro')->name('financeiro.')->group(function () {
        Route::get('/', [FinanceiroController::class, 'index'])->name('index');
        Route::get('/receber', [FinanceiroController::class, 'receber'])->name('receber');
        Route::match(['post', 'patch'], '/receber/{id}/baixa', [FinanceiroController::class, 'baixarReceber'])->name('receber.baixar');
        Route::get('/pagar', [FinanceiroController::class, 'pagar'])->name('pagar');
        Route::post('/pagar', [FinanceiroController::class, 'storePagar'])->name('pagar.store');
        Route::match(['post', 'patch'], '/pagar/{id}/baixa', [FinanceiroController::class, 'baixarPagar'])->name('pagar.baixar');
        Route::get('/repasses', [FinanceiroController::class, 'repasses'])->name('repasses');
        Route::post('/repasses/{id}/pagar', [FinanceiroController::class, 'pagarRepasse'])->name('repasses.pagar');
    });

    // 7. Relatórios Dinâmicos
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::post('/modelos', [RelatorioController::class, 'salvarModelo'])->name('salvar-modelo');
        Route::post('/salvar', [RelatorioController::class, 'salvarModelo'])->name('salvar');
        Route::get('/exportar-csv', [RelatorioController::class, 'exportarCsv'])->name('exportar-csv');
        Route::get('/csv', [RelatorioController::class, 'exportarCsv'])->name('csv');
        Route::get('/imprimir-pdf', [RelatorioController::class, 'imprimirPdf'])->name('imprimir-pdf');
        Route::get('/pdf', [RelatorioController::class, 'imprimirPdf'])->name('pdf');
    });

    // 8. Documentos & Anexos Seguros
    Route::prefix('documentos')->name('documentos.')->group(function () {
        Route::post('/', [DocumentoController::class, 'store'])->name('store');
        Route::get('/{id}/download', [DocumentoController::class, 'download'])->name('download');
        Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('destroy');
    });

    // 9. Convênios & Guias TISS
    Route::prefix('convenios')->name('convenios.')->group(function () {
        Route::get('/', [ConvenioController::class, 'index'])->name('index');
        Route::post('/operadoras', [ConvenioController::class, 'storeConvenio'])->name('store');
        Route::post('/guias', [ConvenioController::class, 'storeGuia'])->name('guias.store');
        Route::match(['post', 'put'], '/guias/{id}', [ConvenioController::class, 'atualizarGuia'])->name('guias.update');
    });

    // 10. Configurações da Clínica & Administração
    Route::prefix('configuracoes')->group(function () {
        Route::get('/', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::put('/', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

        // Salas
        Route::prefix('salas')->name('salas.')->group(function () {
            Route::get('/', [SalaController::class, 'index'])->name('index');
            Route::post('/', [SalaController::class, 'store'])->name('store');
            Route::match(['post', 'put'], '/{id}', [SalaController::class, 'update'])->name('update');
        });

        // Serviços
        Route::prefix('servicos')->name('servicos.')->group(function () {
            Route::get('/', [ServicoController::class, 'index'])->name('index');
            Route::post('/', [ServicoController::class, 'store'])->name('store');
            Route::match(['post', 'put'], '/{id}', [ServicoController::class, 'update'])->name('update');
        });

        // Pacotes Comerciais
        Route::prefix('pacotes')->name('pacotes.')->group(function () {
            Route::get('/', [PacoteController::class, 'index'])->name('index');
            Route::post('/', [PacoteController::class, 'store'])->name('store');
            Route::match(['post', 'put'], '/{id}', [PacoteController::class, 'update'])->name('update');
        });

        // Gestão de Usuários & Perfis
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index');
            Route::post('/', [UsuarioController::class, 'store'])->name('store');
            Route::match(['post', 'put'], '/{id}', [UsuarioController::class, 'update'])->name('update');
            Route::post('/{id}/toggle-status', [UsuarioController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/permissoes', [UsuarioController::class, 'permissoes'])->name('permissoes');
            Route::post('/permissoes/{roleId}', [UsuarioController::class, 'salvarPermissoesRole'])->name('salvar-permissoes');
        });

        // Trilha de Auditoria
        Route::prefix('auditoria')->name('auditoria.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
        });

        // Consulta de Endereço por CEP
        Route::get('/consulta-cep/{cep}', [CepController::class, 'consultar'])->name('cep.consultar');
    });

});
