<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

// Halaman awal langsung menuju login
Route::redirect('/', '/login');

// Route untuk yang sudah login (Grup Auth)
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard: Arahkan ke Controller untuk deteksi role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil (Bisa diakses semua role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route khusus Kader & Admin
    Route::middleware(['role:admin,kader'])->group(function () {
        Route::resource('children', ChildController::class)->except('show');
        Route::resource('measurements', MeasurementController::class);
        Route::get('/api/measurements/{child_id}', [MeasurementController::class, 'getChartData']);
        Route::get('/measurements/pdf', [MeasurementController::class, 'generatePdf'])->name('measurements.pdf');
        Route::post('/measurements/pdf', [MeasurementController::class, 'exportPDF'])->name('measurements.pdf');
        Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
        Route::post('/parents', [ParentController::class, 'store'])->name('parents.store');
        Route::get('/parents/{parent}/edit', [ParentController::class, 'edit'])->name('parents.edit');
        Route::patch('/parents/{parent}', [ParentController::class, 'update'])->name('parents.update');
        Route::delete('/parents/{parent}', [ParentController::class, 'destroy'])->name('parents.destroy');
    });

    Route::middleware(['role:admin,kader,bidan,orangtua'])->group(function () {

        Route::get('/children/{child}', [ChildController::class, 'show'])
            ->name('children.show');
    });

    // Route khusus Orang Tua
    Route::middleware(['role:orangtua'])->group(function () {
        // Contoh: Orang tua hanya bisa melihat data anak mereka sendiri
        Route::get('/my-child', [ChildController::class, 'myChild'])->name('children.myChild');
    });
});

require __DIR__ . '/auth.php';
