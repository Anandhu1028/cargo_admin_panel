<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\RateCalculatorController;
use App\Http\Controllers\Admin\RoeSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All web routes for your admin panel and authentication.
|
*/

//  Default route (optional: can redirect to admin login or dashboard)
Route::get('/', function () {
    return redirect()->route('admin.dashboard.index');
});



//  Admin routes (protected with 'auth' middleware)
Route::middleware(['auth'])->prefix('admin')->group(function () {

    //  Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');

    //  Customers CRUD
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    //  Rate Calculator
    Route::get('rate-calculator', [RateCalculatorController::class, 'index'])->name('rate.index');
    Route::post('rate-calculator', [RateCalculatorController::class, 'calculate'])->name('rate.calculate');

    // Rate Calculation History
    Route::get('rate-history', [RateCalculatorController::class, 'history'])->name('rate.history');
    Route::delete('rate-history/{id}', [RateCalculatorController::class, 'deleteReport'])->name('rate.history.delete');

    



     // Multi-step rate calculator
    Route::get('rate-calculator/step1', [RateCalculatorController::class, 'rateStep1'])->name('rate.step1');
    Route::post('rate-calculator/step1', [RateCalculatorController::class, 'rateStep1Store'])->name('rate.step1.store');

    Route::get('rate-calculator/step2/{calc}', [RateCalculatorController::class, 'rateStep2'])->name('rate.step2');
    Route::post('rate-calculator/step2/{calc}', [RateCalculatorController::class, 'rateStep2Store'])->name('rate.step2.store');

     Route::get('/step3/{calc}', [RateCalculatorController::class, 'rateStep3'])->name('rate.step3');
    Route::post('/step3/{calc}', [RateCalculatorController::class, 'rateStep3Store'])->name('rate.step3.store');


   Route::get('/rate/{id}/report/full', [RateCalculatorController::class, 'rateReportFull'])->name('rate.report.full');

   // ROE Settings
    Route::get('settings/roe', [RoeSettingsController::class, 'index'])->name('roe.index');
    Route::post('settings/roe', [RoeSettingsController::class, 'store'])->name('roe.store');
    Route::delete('settings/roe/{id}', [RoeSettingsController::class, 'destroy'])->name('roe.destroy');
    Route::get('api/roe/{destination}', [RoeSettingsController::class, 'getByDestination'])->name('roe.api.get');

    Route::post('settings/roe/ocean', [RoeSettingsController::class, 'storeOcean'])->name('roe.ocean.store');





     // Customers (used inside rate section)
    Route::post('rate-calculator/customers', [RateCalculatorController::class, 'storeCustomer'])
        ->name('rate.customers.store');
});

//  Profile routes for authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//  Auth routes (from Breeze/Jetstream)
require __DIR__.'/auth.php';