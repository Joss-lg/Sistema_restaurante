<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PrintJobController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('print-jobs')->group(function () {
    Route::get('/pendientes', [PrintJobController::class, 'pendientes']);
    Route::post('/{id}/marcar-impreso', [PrintJobController::class, 'marcarImpreso']);
    Route::post('/{id}/marcar-error', [PrintJobController::class, 'marcarError']);
});