<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/aronline', [\App\Http\Controllers\Api\ArOnlineWebhookController::class, 'handle']);

Route::post('/webhooks/smtp/bounces', [\App\Http\Controllers\Api\SmtpWebhookController::class, 'handleBounce']);
Route::post('/webhooks/smtp/openings', [\App\Http\Controllers\Api\SmtpWebhookController::class, 'handleOpening']);
