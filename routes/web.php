<?php
// routes/web.php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\GameGeneratorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BacktestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Setup\AdminSetupController;
use App\Http\Controllers\Admin\ImportController;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/setup/admin',  [AdminSetupController::class, 'show'])->name('setup.admin');
Route::post('/setup/admin', [AdminSetupController::class, 'store']);

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios');
        Route::post('/usuarios', [UserController::class, 'store'])->name('admin.usuarios.store');
        Route::delete('/admin/usuarios/{user}', [UserController::class, 'destroy'])
        ->name('admin.usuarios.destroy');

        Route::get('/admin/configuracoes',           [SettingController::class, 'index'])->name('admin.configuracoes');
        Route::put('/admin/configuracoes/{tab}',     [SettingController::class, 'update'])->name('admin.configuracoes.update');

        Route::get('/admin/importacoes',              [ImportController::class, 'index'])->name('admin.importacoes');
        Route::post('/admin/importacoes',             [ImportController::class, 'store'])->name('admin.importacoes.store');
        Route::get('/admin/importacoes/{importacao}/status', [ImportController::class, 'status'])->name('admin.importacoes.status');
    });
    Route::get('/', [GameGeneratorController::class, 'dashboard'])->name('dashboard');
    Route::get('/gerador', [GameGeneratorController::class, 'index'])->name('generator.index');
    Route::post('/gerar', [GameGeneratorController::class, 'generate'])->name('generator.generate');

    Route::prefix('backtest')->name('backtest.')->group(function () {
        Route::get('/',                  [BacktestController::class, 'page'])->name('page');
        Route::post('/enqueue',          [BacktestController::class, 'enqueue'])->name('enqueue');
        Route::get('/status/{jobId}',    [BacktestController::class, 'status'])->name('status');
        Route::get('/resultado/{jobId}', [BacktestController::class, 'resultado'])->name('resultado');
        Route::get('/honesto',           [BacktestController::class, 'honesto'])->name('honesto');
        Route::get('/rapido',            [BacktestController::class, 'rapido'])->name('rapido');
    });
});
