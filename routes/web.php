<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\MedicineStockController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Endpoint AJAX untuk chart
    Route::get('/dashboard/get-chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chartData');

    // Visitor Routes
    Route::get('/visitors/export', [VisitorController::class, 'export'])->name('visitors.export');
    Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
    Route::get('/visitors/create', [VisitorController::class, 'create'])->name('visitors.create');
    Route::post('/visitors', [VisitorController::class, 'store'])->name('visitors.store');
    Route::get('/visitors/{id}', [VisitorController::class, 'show'])->name('visitors.show');
    Route::get('/visitors/{id}/edit', [VisitorController::class, 'edit'])->name('visitors.edit');
    Route::put('/visitors/{id}', [VisitorController::class, 'update'])->name('visitors.update');
    Route::delete('/visitors/{id}', [VisitorController::class, 'destroy'])->name('visitors.destroy');

    // Medicine Stock Routes
    Route::get('/medicine-stocks', [MedicineStockController::class, 'index'])->name('medicine-stocks.index');
    Route::get('/medicine-stocks/create', [MedicineStockController::class, 'create'])->name('medicine-stocks.create');
    Route::post('/medicine-stocks', [MedicineStockController::class, 'store'])->name('medicine-stocks.store');
    Route::get('/medicine-stocks/export', [MedicineStockController::class, 'export'])->name('medicine-stocks.export');
    // CRUD logs
    Route::get('/medicine-stocks/edit/{id}', [MedicineStockController::class, 'edit'])->name('medicine-stocks.edit');
    Route::put('/medicine-stocks/{id}', [MedicineStockController::class, 'update'])->name('medicine-stocks.update');
    Route::get('/medicine-stocks/logs', [MedicineStockController::class, 'logs'])->name('medicine-stocks.logs');
    Route::get('/medicine-stocks/{id}', [MedicineStockController::class, 'show'])->name('medicine-stocks.show');
    Route::delete('/medicine-stocks/{id}', [MedicineStockController::class, 'destroy'])->name('medicine-stocks.destroy');
    // AJAX check medicine stock
    Route::get('/medicine-stock/check/{id}', [MedicineStockController::class, 'checkStock']);

    // Medicine Routes
    Route::get('/medicines/create', [MedicineStockController::class, 'createMedicine'])->name('medicines.create');
    Route::post('/medicines', [MedicineStockController::class, 'storeMedicine'])->name('medicines.store');
    Route::get('/medicines/{id}/edit', [MedicineStockController::class, 'editMedicine'])->name('medicines.edit');
    Route::put('/medicines/{id}', [MedicineStockController::class, 'updateMedicine'])->name('medicines.update');
    Route::delete('/medicines/{id}', [MedicineStockController::class, 'destroyMedicine'])->name('medicines.destroy');
    
});
