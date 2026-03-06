<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Eventos\EventController;
use App\Http\Controllers\Protocolos\ProtocoloController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/eventos', [EventController::class, 'index'])->name('eventos.index')->middleware('can:eventos.visualizar');
    Route::post('/eventos', [EventController::class, 'store'])->name('eventos.store')->middleware('can:eventos.criar');
    Route::put('/eventos/{evento}', [EventController::class, 'update'])->name('eventos.update')->middleware('can:eventos.criar');
    Route::get('/eventos/{evento}', [EventController::class, 'show'])->name('eventos.show')->middleware('can:eventos.visualizar');
    Route::patch('/eventos/{evento}/status', [EventController::class, 'toggleStatus'])->name('eventos.toggleStatus')->middleware('can:eventos.criar');
    Route::get('/eventos/{evento}/relatorio', [EventController::class, 'report'])->name('eventos.report')->middleware('can:eventos.relatorio');
    Route::get('/eventos/convidados/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'getConvidados'])->name('convites.getConvidados');
    Route::post('/eventos/{evento}/convites', [App\Http\Controllers\Eventos\InvitationController::class, 'store'])->name('convites.store');
    Route::delete('/convites/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'destroy'])->name('convites.destroy');
    Route::put('/convites/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'update'])->name('convites.update');
    Route::post('/convites/{convite}/convidados', [App\Http\Controllers\Eventos\InvitationController::class, 'storeConvidado'])->name('convidados.store');
    Route::delete('/convidados/{convidado}', [App\Http\Controllers\Eventos\InvitationController::class, 'destroyConvidado'])->name('convidados.destroy');
    Route::put('/convidados/{convidado}', [App\Http\Controllers\Eventos\InvitationController::class, 'updateConvidado'])->name('convidados.update');

    // Lotes de Convite
    Route::post('/eventos/{evento}/lotes', [App\Http\Controllers\Eventos\BatchController::class, 'store'])->name('lotes.store');
    Route::put('/lotes/{lote}', [App\Http\Controllers\Eventos\BatchController::class, 'update'])->name('lotes.update');
    Route::delete('/lotes/{lote}', [App\Http\Controllers\Eventos\BatchController::class, 'destroy'])->name('lotes.destroy');

    // Cadastro de Empresas e Contatos (Clientes)
    Route::resource('empresas', App\Http\Controllers\Cadastro\EmpresaController::class)->middleware('can:empresas.visualizar');
    Route::resource('clientes', App\Http\Controllers\Cadastro\ClienteController::class)->middleware('can:clientes.visualizar');
    Route::resource('tipos_clientes', App\Http\Controllers\Cadastro\TipoClienteController::class)->except(['create', 'show', 'edit'])->middleware('can:tipos_clientes.visualizar');
    Route::resource('regioes', \App\Http\Controllers\Cadastro\RegiaoController::class)->parameters([
        'regioes' => 'regiao'
    ])->middleware('can:regioes.visualizar');

    // Tipos de Protocolo
    Route::get('/protocolos/tipos', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'index'])->name('protocolos.tipos.index');
    Route::post('/protocolos/tipos', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'store'])->name('protocolos.tipos.store');
    Route::put('/protocolos/tipos/{tipo}', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'update'])->name('protocolos.tipos.update');
    Route::delete('/protocolos/tipos/{tipo}', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'destroy'])->name('protocolos.tipos.destroy');

    // Protocolos e AR-Online
    Route::patch('/protocolos/{protocolo}/finalizar', [ProtocoloController::class, 'finalizar'])->name('protocolos.finalizar')->middleware('can:protocolos.finalizar');
    Route::get('/protocolos/{protocolo}/comprovante/{envio}', [ProtocoloController::class, 'baixarComprovante'])->name('protocolos.comprovante')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/{protocolo}/laudo/{envio}', [ProtocoloController::class, 'baixarLaudoPericial'])->name('protocolos.laudo')->middleware('can:protocolos.visualizar');
    Route::get('/protocolos/{protocolo}/sync', [ProtocoloController::class, 'syncStatus'])->name('protocolos.syncStatus')->middleware('can:protocolos.sincronizar');
    Route::resource('/protocolos', ProtocoloController::class)->middleware('can:protocolos.visualizar');

    // AGENDA COLONIA
    Route::prefix('agenda')->name('agenda.')->group(function () {
        // Colônias e Acomodações
        Route::resource('colonias', \App\Http\Controllers\Agenda\ColoniaController::class)->middleware('can:colonias.visualizar');
        Route::resource('colonias.acomodacoes', \App\Http\Controllers\Agenda\ColoniaAcomodacaoController::class)->shallow()->middleware('can:acomodacoes.visualizar');

        // Períodos e Sorteios
        Route::post('periodos/gerar', [\App\Http\Controllers\Agenda\AgendaPeriodoController::class, 'gerarSemanas'])->name('periodos.gerar')->middleware('can:periodos.gerarsemanas');
        Route::resource('periodos', \App\Http\Controllers\Agenda\AgendaPeriodoController::class)->middleware('can:periodos.visualizar');

        // Hóspedes
        Route::resource('hospedes', \App\Http\Controllers\Agenda\AgendaHospedeController::class)->middleware('can:hospedes.visualizar');

        // Reservas e App (Visão de Planilha será feita no index de reservas)
        Route::post('reservas/{reserva}/promover', [\App\Http\Controllers\Agenda\AgendaReservaController::class, 'promoverVaga'])->name('reservas.promover')->middleware('can:reservas.promover');
        Route::post('reservas/{reserva}/excluir', [\App\Http\Controllers\Agenda\AgendaReservaController::class, 'excluirComMotivo'])->name('reservas.excluir')->middleware('can:reservas.excluir');
        Route::resource('reservas', \App\Http\Controllers\Agenda\AgendaReservaController::class)->middleware('can:reservas.visualizar');

        // Histórico de Exclusões de Reservas
        Route::get('historico', [\App\Http\Controllers\Agenda\AgendaHistoricoController::class, 'index'])->name('historico.index');

        // Inscrições / Gerenciador de Sorteio (módulo opcional)
        Route::get('inscricoes/pdf/debug', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'debug'])->name('inscricoes.pdf.debug');
        Route::get('inscricoes/pdf/teste-simples', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'testeSimples'])->name('inscricoes.pdf.testeSimples');
        Route::get('inscricoes/pdf/guia', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'gerarGuiaPreReserva'])->name('inscricoes.pdf.guia')->middleware('can:inscricoes.visualizar');
        Route::get('inscricoes/pdf/lista', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'gerarListaInscritos'])->name('inscricoes.pdf.lista')->middleware('can:inscricoes.visualizar');

        // Relatórios do Painel de Reservas (Planilha)
        Route::get('reservas/pdf/acomodacoes', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'gerarListaReservas'])->name('reservas.pdf.acomodacoes')->middleware('can:reservas.visualizar');
        Route::get('reservas/pdf/espera', [\App\Http\Controllers\Agenda\AgendaImpressaoController::class, 'gerarListaEspera'])->name('reservas.pdf.espera')->middleware('can:reservas.visualizar');
        Route::resource('inscricoes', \App\Http\Controllers\Agenda\AgendaInscricaoController::class)
            ->only(['index', 'store', 'update', 'destroy'])->middleware('can:inscricoes.visualizar');
    });

    // Endpoint AJAX para buscar contatos da empresa
    Route::get('/empresas/{empresa}/contatos', function (\App\Models\Empresa $empresa) {
        return response()->json($empresa->clientes()->where('ativo', true)->get());
    })->name('empresas.contatos');

    // Administração
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show'])
        ->middleware('can:usuarios.visualizar');

    Route::resource('perfis', \App\Http\Controllers\Admin\PerfilController::class)
        ->parameters(['perfis' => 'perfil'])
        ->except(['show'])
        ->middleware('can:usuarios.visualizar');

    Route::resource('token-deptos', \App\Http\Controllers\Admin\TokenDeptoController::class)
        ->except(['create', 'show', 'edit'])
        ->middleware('can:administrar_usuarios');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'update'])->name('password.change.update');
});
