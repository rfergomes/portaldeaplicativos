<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Eventos\EventController;
use App\Http\Controllers\Protocolos\ProtocoloController;
use App\Http\Controllers\Protocolos\OficioColetivoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Agenda\ColoniaController;
use App\Http\Controllers\Agenda\ColoniaAcomodacaoController;
use App\Http\Controllers\Agenda\AgendaPeriodoController;
use App\Http\Controllers\Agenda\AgendaHospedeController;
use App\Http\Controllers\Agenda\AgendaReservaController;
use App\Http\Controllers\Agenda\AgendaHistoricoController;
use App\Http\Controllers\Agenda\AgendaImpressaoController;
use App\Http\Controllers\Agenda\AgendaInscricaoController;
use App\Http\Controllers\Ativos\AtivoEquipamentoController;
use App\Http\Controllers\Ativos\AtivoLicencaController;
use App\Http\Controllers\Ativos\AtivoEstacaoController;
use App\Http\Controllers\SocioCaixaController;
use App\Http\Controllers\SocioFolhaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Eventos\InvitationController;
use App\Http\Controllers\Eventos\BatchController;
use App\Http\Controllers\Cadastro\EmpresaController;
use App\Http\Controllers\Cadastro\ClienteController;
use App\Http\Controllers\Cadastro\TipoClienteController;
use App\Http\Controllers\Cadastro\RegiaoController;
use App\Http\Controllers\Protocolos\TipoProtocoloController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\TokenDeptoController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\ConvencaoColetivaController;
use App\Http\Controllers\Admin\ConvencaoClausulaController;
use App\Http\Controllers\Ativos\AtivoMovimentacaoController;
use App\Http\Controllers\Ativos\AtivoUsuarioController;
use App\Http\Controllers\Ativos\AtivoDepartamentoController;
use App\Http\Controllers\Ativos\AtivoFabricanteController;
use App\Http\Controllers\Ativos\AtivoMarketplaceController;
use App\Http\Controllers\Ativos\AtivoAquisicaoController;
use App\Http\Controllers\Ativos\AtivoFornecedorController;
use App\Http\Controllers\Ativos\AtivoCessaoController;
use App\Http\Controllers\DemandaController;
use App\Models\Empresa;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rotas web principais do Portal de Aplicativos.
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Recuperação de Senha
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

Route::middleware(['auth', 'force_password_change'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/eventos', [EventController::class, 'index'])->name('eventos.index')->middleware('can:eventos.visualizar');
    Route::post('/eventos', [EventController::class, 'store'])->name('eventos.store')->middleware('can:eventos.criar');
    Route::put('/eventos/{evento}', [EventController::class, 'update'])->name('eventos.update')->middleware('can:eventos.criar');
    Route::get('/eventos/{evento}', [EventController::class, 'show'])->name('eventos.show')->middleware('can:eventos.visualizar');
    Route::patch('/eventos/{evento}/status', [EventController::class, 'toggleStatus'])->name('eventos.toggleStatus')->middleware('can:eventos.criar');
    Route::get('/eventos/{evento}/relatorio', [EventController::class, 'report'])->name('eventos.report')->middleware('can:eventos.relatorio');
    Route::get('/eventos/convidados/{convite}', [InvitationController::class, 'getConvidados'])->name('convites.getConvidados');
    Route::post('/eventos/{evento}/convites', [InvitationController::class, 'store'])->name('convites.store');
    Route::delete('/convites/{convite}', [InvitationController::class, 'destroy'])->name('convites.destroy');
    Route::put('/convites/{convite}', [InvitationController::class, 'update'])->name('convites.update');
    Route::post('/convites/{convite}/convidados', [InvitationController::class, 'storeConvidado'])->name('convidados.store');
    Route::delete('/convidados/{convidado}', [InvitationController::class, 'destroyConvidado'])->name('convidados.destroy');
    Route::put('/convidados/{convidado}', [InvitationController::class, 'updateConvidado'])->name('convidados.update');

    // Lotes de Convite
    Route::post('/eventos/{evento}/lotes', [BatchController::class, 'store'])->name('lotes.store');
    Route::put('/lotes/{lote}', [BatchController::class, 'update'])->name('lotes.update');
    Route::delete('/lotes/{lote}', [BatchController::class, 'destroy'])->name('lotes.destroy');

    // Cadastro de Empresas e Contatos (Clientes)
    Route::post('/empresas/import', [EmpresaController::class, 'import'])->name('empresas.import')->middleware('can:empresas.editar');
    Route::resource('empresas', EmpresaController::class)->middleware('can:empresas.visualizar');
    Route::resource('clientes', ClienteController::class)->middleware('can:clientes.visualizar');
    Route::resource('tipos_clientes', TipoClienteController::class)->except(['create', 'show', 'edit'])->middleware('can:tipos_clientes.visualizar');
    Route::resource('regioes', RegiaoController::class)->parameters([
        'regioes' => 'regiao'
    ])->middleware('can:regioes.visualizar');

    // Controle de Demandas (To-Do Delegável)
    Route::post('/demandas/{demanda}/devolutiva', [DemandaController::class, 'devolutiva'])->name('demandas.devolutiva');
    Route::post('/demandas/{demanda}/reencaminhar', [DemandaController::class, 'reencaminhar'])->name('demandas.reencaminhar');
    Route::post('/demandas/{demanda}/checklists/{checklist}/toggle', [DemandaController::class, 'toggleChecklist'])->name('demandas.checklists.toggle');
    Route::resource('demandas', DemandaController::class);

    // Tipos de Protocolo
    Route::get('/protocolos/tipos', [TipoProtocoloController::class, 'index'])->name('protocolos.tipos.index');
    Route::post('/protocolos/tipos', [TipoProtocoloController::class, 'store'])->name('protocolos.tipos.store');
    Route::put('/protocolos/tipos/{tipo}', [TipoProtocoloController::class, 'update'])->name('protocolos.tipos.update');
    Route::delete('/protocolos/tipos/{tipo}', [TipoProtocoloController::class, 'destroy'])->name('protocolos.tipos.destroy');

    // Ofícios Coletivos
    Route::get('/protocolos/oficios', [OficioColetivoController::class, 'index'])->name('protocolos.oficios.index')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/oficios/criar', [OficioColetivoController::class, 'create'])->name('protocolos.oficios.create')->middleware('can:protocolos.criar');
    Route::post('/protocolos/oficios', [OficioColetivoController::class, 'store'])->name('protocolos.oficios.store')->middleware('can:protocolos.criar');
    Route::get('/protocolos/oficios/api/empresas-filtradas', [OficioColetivoController::class, 'getEmpresasFiltradas'])->name('protocolos.oficios.api.empresas')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/oficios/{protocolo}', [OficioColetivoController::class, 'show'])->name('protocolos.oficios.show')->middleware('can:protocolos.visualizar');
    Route::post('/protocolos/oficios/{protocolo}/disparar-lote', [OficioColetivoController::class, 'dispararLote'])->name('protocolos.oficios.dispararLote')->middleware('can:protocolos.criar');

    // Protocolos e AR-Online
    Route::get('/protocolos/pdf/falhas', [ProtocoloController::class, 'relatorioFalhas'])->name('protocolos.pdf.falhas')->middleware('can:protocolos.visualizar');
    Route::post('/protocolos/{protocolo}/envios/{envio}/reenviar', [ProtocoloController::class, 'reenviarEnvio'])->name('protocolos.envios.reenviar')->middleware('can:protocolos.criar');
    Route::post('/protocolos/{protocolo}/reenviar-falhas', [ProtocoloController::class, 'reenviarFalhas'])->name('protocolos.reenviarFalhas')->middleware('can:protocolos.criar');
    Route::put('/protocolos/{protocolo}/destinatarios/{destinatario}', [ProtocoloController::class, 'updateDestinatario'])->name('protocolos.destinatarios.update')->middleware('can:protocolos.criar');
    Route::get('/protocolos/{protocolo}/envios/{envio}/historico', [ProtocoloController::class, 'historicoEnvio'])->name('protocolos.envios.historico')->middleware('can:protocolos.visualizar');
    Route::patch('/protocolos/{protocolo}/finalizar', [ProtocoloController::class, 'finalizar'])->name('protocolos.finalizar')->middleware('can:protocolos.finalizar');
    Route::get('/protocolos/{protocolo}/comprovante/{envio}', [ProtocoloController::class, 'baixarComprovante'])->name('protocolos.comprovante')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/{protocolo}/laudo/{envio}', [ProtocoloController::class, 'baixarLaudoPericial'])->name('protocolos.laudo')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/{protocolo}/sync', [ProtocoloController::class, 'syncStatus'])->name('protocolos.syncStatus')->middleware('can:protocolos.sincronizar');
    Route::get('/protocolos/{protocolo}/anexos/{anexo}/download', [ProtocoloController::class, 'baixarAnexo'])->name('protocolos.anexos.download')->middleware('can:protocolos.visualizar');
    Route::resource('/protocolos', ProtocoloController::class)->where(['protocolo' => '[0-9]+'])->middleware('can:protocolos.visualizar');

    // AGENDA COLONIA
    Route::prefix('agenda')->name('agenda.')->group(function () {
        // Colônias e Acomodações
        Route::resource('colonias', ColoniaController::class)->middleware(['can:colonias.visualizar', 'uppercase.agenda']);
        Route::resource('colonias.acomodacoes', ColoniaAcomodacaoController::class)->shallow()->middleware(['can:acomodacoes.visualizar', 'uppercase.agenda']);

        // Períodos e Sorteios
        Route::post('periodos/gerar', [AgendaPeriodoController::class, 'gerarSemanas'])->name('periodos.gerar')->middleware('can:periodos.gerarsemanas');
        Route::resource('periodos', AgendaPeriodoController::class)->middleware('can:periodos.visualizar');

        // Hóspedes
        Route::resource('hospedes', AgendaHospedeController::class)->middleware('can:hospedes.visualizar');

        // Reservas e App (Visão de Planilha será feita no index de reservas)
        Route::post('reservas/{reserva}/promover', [AgendaReservaController::class, 'promoverVaga'])->name('reservas.promover')->middleware('can:reservas.promover');
        Route::post('reservas/{reserva}/excluir', [AgendaReservaController::class, 'excluirComMotivo'])->name('reservas.excluir')->middleware('can:reservas.excluir');
        Route::post('reservas/{reserva}/trocar', [AgendaReservaController::class, 'trocarAcomodacao'])->name('reservas.trocar')->middleware('can:reservas.visualizar');
        Route::post('reservas/{reserva}/notificar-whatsapp', [AgendaReservaController::class, 'notificarWhatsApp'])
            ->name('reservas.notificar_whatsapp')
            ->middleware('can:reservas.visualizar');
        Route::resource('reservas', AgendaReservaController::class)->middleware(['can:reservas.visualizar', 'uppercase.agenda']);
        // Histórico de Exclusões de Reservas
        Route::get('historico', [AgendaHistoricoController::class, 'index'])->name('historico.index');

        // Inscrições / Gerenciador de Sorteio (módulo opcional)
        Route::get('inscricoes/pdf/guia', [AgendaImpressaoController::class, 'gerarGuiaPreReserva'])->name('inscricoes.pdf.guia')->middleware('can:inscricoes.visualizar');
        Route::get('inscricoes/pdf/lista', [AgendaImpressaoController::class, 'gerarListaInscritos'])->name('inscricoes.pdf.lista')->middleware('can:inscricoes.visualizar');

        // Relatórios do Painel de Reservas (Planilha)
        Route::get('reservas/pdf/acomodacoes', [AgendaImpressaoController::class, 'gerarListaReservas'])->name('reservas.pdf.acomodacoes')->middleware('can:reservas.visualizar');
        Route::get('reservas/pdf/espera', [AgendaImpressaoController::class, 'gerarListaEspera'])->name('reservas.pdf.espera')->middleware('can:reservas.visualizar');
        Route::resource('inscricoes', AgendaInscricaoController::class)
            ->parameters(['inscricoes' => 'inscricao'])
            ->only(['index', 'store', 'update', 'destroy'])->middleware(['can:inscricoes.visualizar', 'uppercase.agenda']);
    });

    // Endpoint AJAX para buscar contatos da empresa
    Route::get('/empresas/{empresa}/contatos', function (Empresa $empresa) {
        return response()->json($empresa->clientes()->where('ativo', true)->get());
    })->name('empresas.contatos');

    // Administração
    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware('can:usuarios.visualizar');

    Route::resource('perfis', PerfilController::class)
        ->parameters(['perfis' => 'perfil'])
        ->except(['show'])
        ->middleware('can:usuarios.visualizar');

    Route::resource('token-deptos', TokenDeptoController::class)
        ->except(['create', 'show', 'edit'])
        ->middleware('can:administrar_usuarios');

    // Relatórios Administrativos
    Route::prefix('admin/relatorios')->name('admin.relatorios.')->middleware('can:usuarios.visualizar')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::get('/pdf', [RelatorioController::class, 'exportPdf'])->name('pdf');
    });

    // Convenções Coletivas e Cláusulas
    Route::prefix('admin/convencoes')->name('admin.convencoes.')->group(function () {
        Route::get('/', [ConvencaoColetivaController::class, 'index'])->name('index')->middleware('can:convencoes.visualizar');
        Route::get('/create', [ConvencaoColetivaController::class, 'create'])->name('create')->middleware('can:convencoes.criar');
        Route::post('/', [ConvencaoColetivaController::class, 'store'])->name('store')->middleware('can:convencoes.criar');
        Route::get('/{convencao}', [ConvencaoColetivaController::class, 'show'])->name('show')->middleware('can:convencoes.visualizar');
        Route::get('/{convencao}/edit', [ConvencaoColetivaController::class, 'edit'])->name('edit')->middleware('can:convencoes.editar');
        Route::put('/{convencao}', [ConvencaoColetivaController::class, 'update'])->name('update')->middleware('can:convencoes.editar');
        Route::delete('/{convencao}', [ConvencaoColetivaController::class, 'destroy'])->name('destroy')->middleware('can:convencoes.excluir');

        // Gestão de Cláusulas com Scoped Bindings
        Route::scopeBindings()->group(function () {
            Route::post('/{convencao}/clausulas', [ConvencaoClausulaController::class, 'store'])->name('clausulas.store')->middleware('can:convencoes.criar');
            Route::put('/{convencao}/clausulas/{clausula}', [ConvencaoClausulaController::class, 'update'])->name('clausulas.update')->middleware('can:convencoes.editar');
            Route::delete('/{convencao}/clausulas/{clausula}', [ConvencaoClausulaController::class, 'destroy'])->name('clausulas.destroy')->middleware('can:convencoes.excluir');
            Route::patch('/{convencao}/clausulas/{clausula}/toggle-lembrete', [ConvencaoClausulaController::class, 'toggleLembrete'])->name('clausulas.toggle-lembrete')->middleware('can:convencoes.editar');
        });
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/password/change', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
    // Módulo de Controle de Ativos (Ativos)
    Route::prefix('ativos')->name('ativos.')->middleware(['can:ativos.visualizar', 'uppercase.ativos'])->group(function () {
        Route::get('equipamentos/pdf/inventario', [AtivoEquipamentoController::class, 'gerarInventarioPdf'])->name('equipamentos.inventario.pdf');
        Route::get('equipamentos/{equipamento}/pdf/baixa', [AtivoEquipamentoController::class, 'pdfBaixa'])->name('equipamentos.pdf_baixa');
        Route::post('equipamentos/{equipamento}/anexos', [AtivoEquipamentoController::class, 'uploadAnexo'])->name('equipamentos.anexos.store');
        Route::resource('equipamentos', AtivoEquipamentoController::class);
        Route::resource('movimentacoes', AtivoMovimentacaoController::class)->only(['index', 'store']);
        Route::resource('usuarios', AtivoUsuarioController::class);
        Route::resource('departamentos', AtivoDepartamentoController::class)->except(['show']);
        Route::resource('fabricantes', AtivoFabricanteController::class)->except(['show']);
        Route::resource('marketplaces', AtivoMarketplaceController::class)->except(['show']);
        Route::get('aquisicoes/api/equipamentos-para-cessao', [AtivoAquisicaoController::class, 'getEquipamentosDisponiveisPorNfs'])->name('aquisicoes.equipamentos_disponiveis');
        Route::resource('aquisicoes', AtivoAquisicaoController::class);
        Route::post('aquisicoes/{aquisicao}/anexos', [AtivoAquisicaoController::class, 'uploadAnexo'])->name('aquisicoes.anexos.store');
        Route::post('aquisicoes/{aquisicao}/devolver-fornecedor', [AtivoAquisicaoController::class, 'devolverFornecedor'])->name('aquisicoes.devolver_fornecedor');
        Route::resource('fornecedores', AtivoFornecedorController::class)->except(['show']);

        // Gestão de Cessões
        Route::get('cessoes/pdf/relatorio', [AtivoCessaoController::class, 'gerarRelatorioPdf'])->name('cessoes.relatorio.pdf');
        Route::get('cessoes', [AtivoCessaoController::class, 'index'])->name('cessoes.index');
        Route::post('cessoes', [AtivoCessaoController::class, 'store'])->name('cessoes.store');
        Route::get('cessoes/{cessao}/pdf', [AtivoCessaoController::class, 'generatePdf'])->name('cessoes.pdf');
        Route::get('cessoes/{cessao}/pdf-devolucao', [AtivoCessaoController::class, 'generatePdfDevolucao'])->name('cessoes.pdf_devolucao');
        Route::post('cessoes/{cessao}/devolver', [AtivoCessaoController::class, 'processarDevolucao'])->name('cessoes.devolver');
        Route::post('cessoes/{cessao}/reverter', [AtivoCessaoController::class, 'reverterCessao'])->name('cessoes.reverter');
        Route::get('movimentacoes/{movimentacao}/pdf/devolucao', [AtivoMovimentacaoController::class, 'pdfDevolucao'])->name('devolucao.pdf');
        Route::post('cessoes/{cessao}/anexos', [AtivoCessaoController::class, 'uploadAnexo'])->name('cessoes.anexos.store');
        Route::get('anexos/{anexo}/download/{filename?}', [AtivoCessaoController::class, 'downloadAnexo'])->name('anexos.download');
        Route::delete('anexos/{anexo}', [AtivoCessaoController::class, 'destroyAnexo'])->name('anexos.destroy');
        
        // Novas rotas de Inventário e Licenças
        Route::get('licencas/aquisicao', [AtivoLicencaController::class, 'createAquisicao'])->name('licencas.create_aquisicao');
        Route::post('licencas/aquisicao', [AtivoLicencaController::class, 'storeAquisicao'])->name('licencas.store_aquisicao');
        Route::resource('licencas', AtivoLicencaController::class);
        Route::post('licencas/{licenca}/anexos', [AtivoLicencaController::class, 'uploadAnexo'])->name('licencas.anexos.store');
        Route::post('licencas/{equipamento}/vincular', [AtivoLicencaController::class, 'vincularEquipamento'])->name('licencas.vincular');
        Route::delete('licencas/{licenca}/{equipamento}/desvincular', [AtivoLicencaController::class, 'desvincularEquipamento'])->name('licencas.desvincular');
        
        Route::get('estacoes/pdf', [AtivoEstacaoController::class, 'gerarPdf'])->name('estacoes.pdf');
        Route::resource('estacoes', AtivoEstacaoController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['estacoes' => 'estacao']);

        // API endpoints
        Route::get('api/estacoes', [AtivoEstacaoController::class, 'apiGetEstacoes'])->name('api.estacoes');
    });

    // Sócio Caixa
    Route::prefix('socio-caixa')->name('socios-caixa.')->group(function () {
        Route::get('/', [SocioCaixaController::class, 'index'])->name('index')->middleware('can:socio_caixa.visualizar');
        Route::get('/dashboard', [SocioCaixaController::class, 'dashboard'])->name('dashboard')->middleware('can:socio_caixa.visualizar');
        Route::post('/import', [SocioCaixaController::class, 'import'])->name('import')->middleware('can:socio_caixa.importar');
        Route::patch('/{socio}/toggle-payment', [SocioCaixaController::class, 'togglePayment'])->name('toggle-payment')->middleware('can:socio_caixa.gerenciar');
        Route::patch('/{socio}/postpone', [SocioCaixaController::class, 'postpone'])->name('postpone')->middleware('can:socio_caixa.gerenciar');
        Route::patch('/{socio}/inativar-abaco', [SocioCaixaController::class, 'inativarAbaco'])->name('inativar-abaco')->middleware('can:socio_caixa.gerenciar');
        Route::patch('/{socio}/reativar-abaco', [SocioCaixaController::class, 'reativarAbaco'])->name('reativar-abaco')->middleware('can:socio_caixa.gerenciar');
        Route::patch('/{socio}/update-telefone', [SocioCaixaController::class, 'updateTelefone'])->name('update-telefone')->middleware('can:socio_caixa.gerenciar');
        Route::post('/{socio}/enviar-whatsapp', [SocioCaixaController::class, 'enviarWhatsapp'])->name('enviar-whatsapp')->middleware('can:socio_caixa.gerenciar');
        Route::get('/{socio}', [SocioCaixaController::class, 'show'])->name('show')->middleware('can:socio_caixa.visualizar');
        Route::post('/ocorrencias', [SocioCaixaController::class, 'storeOcorrencia'])->name('ocorrencias.store')->middleware('can:socio_caixa.ocorrencias');
    });

    Route::get('/run-permissions-seeder', function() {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DashboardPermissionsSeeder']);
        return 'Seeder executado! O perfil Administrador agora deve ter acesso ao Dashboard Macro. <a href="/dashboard">Voltar ao Dashboard</a>';
    });

    // Sócio Folha
    Route::prefix('socio-folha')->name('socios-folha.')->group(function () {
        Route::get('/', [SocioFolhaController::class, 'index'])->name('index')->middleware('can:socio_folha.visualizar');
        Route::post('/import', [SocioFolhaController::class, 'import'])->name('import')->middleware('can:socio_folha.importar');
        Route::patch('/{socio}/toggle-situacao', [SocioFolhaController::class, 'toggleSituacao'])->name('toggle-situacao')->middleware('can:socio_folha.gerenciar');
        Route::patch('/{socio}/toggle-lista', [SocioFolhaController::class, 'toggleLista'])->name('toggle-lista')->middleware('can:socio_folha.gerenciar');
        Route::patch('/{socio}/toggle-baixa', [SocioFolhaController::class, 'toggleBaixa'])->name('toggle-baixa')->middleware('can:socio_folha.gerenciar');
        Route::get('/empresas-por-regiao/{regiao_id}', [SocioFolhaController::class, 'getEmpresasPorRegiao'])->name('empresas-por-regiao');
        Route::get('/pdf/pendentes', [SocioFolhaController::class, 'exportPendentesPdf'])->name('pdf.pendentes')->middleware('can:socio_folha.visualizar');
        Route::get('/pdf/pendentes-lista-baixa', [SocioFolhaController::class, 'exportPendentesListaBaixaPdf'])->name('pdf.pendentes_lista_baixa')->middleware('can:socio_folha.visualizar');
        Route::get('/pdf/empresa/{empresa_id}', [SocioFolhaController::class, 'exportEmpresaDebitosPdf'])->name('pdf.empresa')->middleware('can:socio_folha.visualizar');
        Route::get('/{socio}/email-historico', [SocioFolhaController::class, 'getEmailHistorico'])->name('email-historico')->middleware('can:socio_folha.visualizar');
        Route::get('/{socio}/historico-alteracoes', [SocioFolhaController::class, 'getHistoricoAlteracoes'])->name('historico-alteracoes')->middleware('can:socio_folha.visualizar');
    });
});


Route::post('/_deploy/opcache-reset', function () {

    if (!in_array(request()->ip(), ['127.0.0.1', '::1'])) {
        abort(403);
    }

    if (function_exists('opcache_reset')) {
        opcache_reset();
        return ['status' => 'ok', 'message' => 'OPcache resetado com sucesso'];
    }

    return response()->json(['status' => 'ok']);
});

Route::get('/_health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok'], 200);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error'], 500);
    }
});
Route::get('/version', fn() => now());