<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TypeOperationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::patch('/clients/{client}/statut', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');

    Route::get('/operations', [OperationController::class, 'index'])->name('operations.index');
    Route::get('/operations/{operation}', [OperationController::class, 'show'])->name('operations.show');
    Route::get('/operations/{operation}/recu', [OperationController::class, 'recu'])->name('operations.recu');
    Route::get('/operations/{operation}/correction', [OperationController::class, 'createCorrection'])->name('operations.correction.create');
    Route::post('/operations/{operation}/correction', [OperationController::class, 'storeCorrection'])->name('operations.correction.store');

    Route::get('/credits', [OperationController::class, 'credits'])->name('credits.index');
    Route::get('/credits/create', [OperationController::class, 'createCredit'])->name('credits.create');
    Route::post('/credits', [OperationController::class, 'storeCredit'])->name('credits.store');
    Route::get('/remboursements', [OperationController::class, 'remboursements'])->name('remboursements.index');
    Route::get('/remboursements/create', [OperationController::class, 'createRemboursement'])->name('remboursements.create');
    Route::post('/remboursements', [OperationController::class, 'storeRemboursement'])->name('remboursements.store');

    Route::get('/rapports/portefeuille', [RapportController::class, 'portefeuille'])->name('rapports.portefeuille');
    Route::get('/rapports/mensuel', [RapportController::class, 'mensuel'])->name('rapports.mensuel');
    Route::get('/rapports/debiteurs', [RapportController::class, 'debiteurs'])->name('rapports.debiteurs');
    Route::get('/rapports/historique', [RapportController::class, 'historique'])->name('rapports.historique');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::resource('type-operations', TypeOperationController::class)->except(['show', 'destroy']);
        Route::get('/administration/parametres', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/administration/parametres', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/administration/audit', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
