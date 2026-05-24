<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\PurchaseForm;
use App\Livewire\PurchaseList;
use App\Livewire\RunMigrations;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('purchases.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/purchases', PurchaseList::class)->name('purchases.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('/purchases/create', PurchaseForm::class)->name('purchases.create');
        Route::get('/purchases/{purchase}/edit', PurchaseForm::class)->name('purchases.edit');
        Route::get('/admin/migrations', RunMigrations::class)->name('admin.migrations');
    });
});
