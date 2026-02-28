<?php

use App\Http\Controllers\Auth\LoginController;
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

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/eventos', [EventController::class, 'index'])->name('eventos.index');
    Route::post('/eventos', [EventController::class, 'store'])->name('eventos.store');
    Route::get('/eventos/{evento}', [EventController::class, 'show'])->name('eventos.show');
    Route::get('/eventos/{evento}/relatorio', [EventController::class, 'report'])->name('eventos.report');
    Route::get('/eventos/convidados/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'getConvidados'])->name('convites.getConvidados');
    Route::post('/eventos/{evento}/convites', [App\Http\Controllers\Eventos\InvitationController::class, 'store'])->name('convites.store');
    Route::delete('/convites/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'destroy'])->name('convites.destroy');
    Route::put('/convites/{convite}', [App\Http\Controllers\Eventos\InvitationController::class, 'update'])->name('convites.update');
    Route::post('/convites/{convite}/convidados', [App\Http\Controllers\Eventos\InvitationController::class, 'storeConvidado'])->name('convidados.store');
    Route::delete('/convidados/{convidado}', [App\Http\Controllers\Eventos\InvitationController::class, 'destroyConvidado'])->name('convidados.destroy');
    Route::put('/convidados/{convidado}', [App\Http\Controllers\Eventos\InvitationController::class, 'updateConvidado'])->name('convidados.update');

    // Cadastro de Empresas e Contatos (Clientes)
    Route::resource('empresas', App\Http\Controllers\Cadastro\EmpresaController::class);
    Route::resource('clientes', App\Http\Controllers\Cadastro\ClienteController::class);
    Route::resource('tipos_clientes', App\Http\Controllers\Cadastro\TipoClienteController::class)->except(['create', 'show', 'edit']);
    Route::resource('regioes', \App\Http\Controllers\Cadastro\RegiaoController::class)->parameters([
        'regioes' => 'regiao'
    ]);

    // Tipos de Protocolo
    Route::get('/protocolos/tipos', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'index'])->name('protocolos.tipos.index');
    Route::post('/protocolos/tipos', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'store'])->name('protocolos.tipos.store');
    Route::put('/protocolos/tipos/{tipo}', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'update'])->name('protocolos.tipos.update');
    Route::delete('/protocolos/tipos/{tipo}', [\App\Http\Controllers\Protocolos\TipoProtocoloController::class, 'destroy'])->name('protocolos.tipos.destroy');

    // Protocolos e AR-Online
    Route::get('/protocolos/{protocolo}/sync-status', [ProtocoloController::class, 'syncStatus'])->name('protocolos.syncStatus');
    Route::get('/protocolos/{protocolo}/comprovante/{envio}', [ProtocoloController::class, 'baixarComprovante'])->name('protocolos.comprovante');
    Route::get('/protocolos/{protocolo}/laudo/{envio}', [ProtocoloController::class, 'baixarLaudo'])->name('protocolos.laudo');
    Route::resource('protocolos', ProtocoloController::class)->only(['index', 'create', 'store', 'show']);

    // Endpoint AJAX para buscar contatos da empresa
    Route::get('/empresas/{empresa}/contatos', function (\App\Models\Empresa $empresa) {
        return response()->json($empresa->clientes()->where('ativo', true)->get());
    })->name('empresas.contatos');

    // Administração
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show'])
        ->middleware('can:administrar_usuarios');

    Route::resource('token-deptos', \App\Http\Controllers\Admin\TokenDeptoController::class)
        ->except(['create', 'show', 'edit'])
        ->middleware('can:administrar_usuarios');
});
