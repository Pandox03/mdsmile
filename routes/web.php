<?php

use App\Http\Controllers\CalendrierController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\FacturesController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\PrestationsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TravauxController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');

    // Manager + Secrétaire + Assistante + CAD/CAM: travaux create/edit (CAD/CAM sans champ prix)
    Route::middleware(['role:manager|secretaire|assistante|cadcam'])->group(function () {
        Route::get('/travaux/create', [TravauxController::class, 'create'])->name('travaux.create');
        Route::post('/travaux', [TravauxController::class, 'store'])->name('travaux.store');
        Route::get('/travaux/{travail}/edit', [TravauxController::class, 'edit'])->name('travaux.edit');
        Route::put('/travaux/{travail}', [TravauxController::class, 'update'])->name('travaux.update');
        Route::get('/travaux/{travail}/add-phase', [TravauxController::class, 'addPhaseForm'])->name('travaux.add-phase');
        Route::post('/travaux/{travail}/add-phase', [TravauxController::class, 'storePhase'])->name('travaux.add-phase.store');
        Route::patch('/travaux/{travail}/statut', [TravauxController::class, 'updateStatut'])->name('travaux.updateStatut');
        Route::delete('/travaux/{travail}', [TravauxController::class, 'destroy'])->name('travaux.destroy');
    });

    // Manager + Secrétaire + Assistante + CAD/CAM: travaux list and detail (read-only for CAD/CAM), calendrier
    Route::middleware(['role:manager|secretaire|assistante|cadcam'])->group(function () {
        Route::get('/travaux', [TravauxController::class, 'index'])->name('travaux.index');
        Route::get('/travaux/{travail}', [TravauxController::class, 'show'])->name('travaux.show');
        Route::get('/calendrier', [CalendrierController::class, 'index'])->name('calendrier.index');
    });

    // Manager + Secrétaire + Assistante: caisse
    Route::middleware(['role:manager|secretaire|assistante'])->group(function () {
        Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.index');
        Route::get('/caisse/report', [CaisseController::class, 'report'])->name('caisse.report');
        Route::get('/caisse/create', [CaisseController::class, 'create'])->name('caisse.create');
        Route::post('/caisse', [CaisseController::class, 'store'])->name('caisse.store');
        Route::get('/caisse/{caisseMouvement}/edit', [CaisseController::class, 'edit'])->name('caisse.edit');
        Route::put('/caisse/{caisseMouvement}', [CaisseController::class, 'update'])->name('caisse.update');
        Route::delete('/caisse/{caisseMouvement}', [CaisseController::class, 'destroy'])->name('caisse.destroy');
    });

    // Manager + Secrétaire only: clients (lecture), factures, stock, situations doc
    Route::middleware(['role:manager|secretaire'])->group(function () {
        Route::get('/clients', [DocsController::class, 'index'])->name('clients.index');
        // Specific client routes first so "create" and "{id}/edit" are not matched by /clients/{doc}
        Route::get('/clients/create', [DocsController::class, 'create'])->name('clients.create')->middleware('role:manager|secretaire');
        Route::post('/clients', [DocsController::class, 'store'])->name('clients.store')->middleware('role:manager|secretaire');
        Route::get('/clients/{doc}/edit', [DocsController::class, 'edit'])->name('clients.edit')->middleware('role:manager');
        Route::put('/clients/{doc}', [DocsController::class, 'update'])->name('clients.update')->middleware('role:manager');
        Route::delete('/clients/{doc}', [DocsController::class, 'destroy'])->name('clients.destroy')->middleware('role:manager');
        Route::get('/clients/{doc}/prestations', [DocsController::class, 'clientPrestations'])->name('clients.prestations')->middleware('role:manager|secretaire');
        Route::put('/clients/{doc}/prestations', [DocsController::class, 'updateClientPrestations'])->name('clients.prestations.update')->middleware('role:manager|secretaire');
        Route::get('/clients/{doc}', [DocsController::class, 'show'])->name('clients.show');
        Route::get('/doc-situations', [DocsController::class, 'situationsIndex'])->name('doc.situations.index');
        Route::get('/doc-situations/pdf', [DocsController::class, 'situationsPdf'])->name('doc.situations.pdf');
        Route::post('/doc-situations/encaissement', [DocsController::class, 'storeSituationEncaissement'])->name('doc.situations.encaissement');
        Route::delete('/doc-situations/encaissement/{docSituationEncaissement}', [DocsController::class, 'destroySituationEncaissement'])->name('doc.situations.encaissement.destroy');
        Route::get('/factures', [FacturesController::class, 'index'])->name('factures.index');
        Route::get('/factures/create', [FacturesController::class, 'create'])->name('factures.create');
        Route::post('/factures', [FacturesController::class, 'store'])->name('factures.store');
        Route::post('/factures/regrouper', [FacturesController::class, 'regrouper'])->name('factures.regrouper');
        Route::get('/factures/{facture}/pdf', [FacturesController::class, 'pdf'])->name('factures.pdf');
        Route::get('/factures/{facture}/edit', [FacturesController::class, 'edit'])->name('factures.edit');
        Route::put('/factures/{facture}', [FacturesController::class, 'update'])->name('factures.update');
        Route::delete('/factures/{facture}', [FacturesController::class, 'destroy'])->name('factures.destroy');
        Route::get('/factures/{facture}', [FacturesController::class, 'show'])->name('factures.show');
    });

    // Manager + Secrétaire + Technicien (cadcam): stock index + materials edit/update only (no fournisseurs)
    Route::middleware(['role:manager|secretaire|cadcam'])->group(function () {
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/materials/{stock}/edit', [StockController::class, 'editMaterial'])->name('stock.materials.edit');
        Route::put('/stock/materials/{stock}', [StockController::class, 'updateMaterial'])->name('stock.materials.update');
    });

    // Manager + Secrétaire only: stock fournisseurs + materials create/delete
    Route::middleware(['role:manager|secretaire'])->group(function () {
        Route::get('/stock/materials/create', [StockController::class, 'createMaterial'])->name('stock.materials.create');
        Route::post('/stock/materials', [StockController::class, 'storeMaterial'])->name('stock.materials.store');
        Route::delete('/stock/materials/{stock}', [StockController::class, 'destroyMaterial'])->name('stock.materials.destroy');
        Route::get('/stock/fournisseurs/create', [StockController::class, 'createFournisseur'])->name('stock.fournisseurs.create');
        Route::post('/stock/fournisseurs', [StockController::class, 'storeFournisseur'])->name('stock.fournisseurs.store');
        Route::get('/stock/fournisseurs/{fournisseur}/edit', [StockController::class, 'editFournisseur'])->name('stock.fournisseurs.edit');
        Route::put('/stock/fournisseurs/{fournisseur}', [StockController::class, 'updateFournisseur'])->name('stock.fournisseurs.update');
        Route::delete('/stock/fournisseurs/{fournisseur}', [StockController::class, 'destroyFournisseur'])->name('stock.fournisseurs.destroy');
    });

    // Manager + Secrétaire: prestations
    Route::middleware(['role:manager|secretaire'])->group(function () {
        Route::get('/prestations', [PrestationsController::class, 'index'])->name('prestations.index');
        Route::get('/prestations/categories/create', [PrestationsController::class, 'createCategory'])->name('prestations.categories.create');
        Route::post('/prestations/categories', [PrestationsController::class, 'storeCategory'])->name('prestations.categories.store');
        Route::get('/prestations/categories/{prestationCategory}/edit', [PrestationsController::class, 'editCategory'])->name('prestations.categories.edit');
        Route::put('/prestations/categories/{prestationCategory}', [PrestationsController::class, 'updateCategory'])->name('prestations.categories.update');
        Route::delete('/prestations/categories/{prestationCategory}', [PrestationsController::class, 'destroyCategory'])->name('prestations.categories.destroy');
        Route::get('/prestations/create', [PrestationsController::class, 'createPrestation'])->name('prestations.create');
        Route::post('/prestations', [PrestationsController::class, 'storePrestation'])->name('prestations.store');
        Route::get('/prestations/{prestation}/edit', [PrestationsController::class, 'editPrestation'])->name('prestations.edit');
        Route::put('/prestations/{prestation}', [PrestationsController::class, 'updatePrestation'])->name('prestations.update');
        Route::delete('/prestations/{prestation}', [PrestationsController::class, 'destroyPrestation'])->name('prestations.destroy');
    });

    // Manager only: paramètres, journaux, utilisateurs, internes, montant comptabilisé
    Route::middleware(['role:manager'])->group(function () {
        Route::get('/parametres', [ParametresController::class, 'index'])->name('parametres.index');
        Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
        Route::get('/factures-internes', [FacturesController::class, 'internesIndex'])->name('factures.internes.index');
        Route::get('/factures/{facture}/internes', [FacturesController::class, 'internes'])->name('factures.internes');
        Route::patch('/travaux/{travail}/montant-comptabilise', [TravauxController::class, 'updateMontantComptabilise'])->name('travaux.updateMontantComptabilise');
        Route::patch('/factures/{facture}/montant-comptabilise', [FacturesController::class, 'updateMontantComptabilise'])->name('factures.updateMontantComptabilise');
    });

    Route::middleware(['role:manager'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/create', [UsersController::class, 'create'])->name('create');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UsersController::class, 'update'])->name('update');
        Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
