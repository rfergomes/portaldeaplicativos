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

    Route::get('/protocolos', [ProtocoloController::class, 'index'])->name('protocolos.index');
    Route::get('/protocolos/novo', [ProtocoloController::class, 'create'])->name('protocolos.create');
    Route::post('/protocolos', [ProtocoloController::class, 'store'])->name('protocolos.store');
});
