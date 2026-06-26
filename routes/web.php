<?php

use App\Http\Controllers\AdminResourceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenResourceController;
use App\Http\Controllers\ProdiDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/login', 'pages::auth.login')->name('login');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminResourceController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/{resource}', [AdminResourceController::class, 'store'])->name('resources.store');
    Route::put('/dashboard/{resource}/{id}', [AdminResourceController::class, 'update'])->name('resources.update');
    Route::delete('/dashboard/{resource}/{id}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
    Route::get('/laporan', [AdminResourceController::class, 'laporanIndex'])->name('laporan.index');
    Route::post('/laporan/generate', [AdminResourceController::class, 'laporanGenerate'])->name('laporan.generate');
    Route::post('/logout', [DashboardController::class, 'logoutAdmin'])->name('logout');
});

Route::middleware('dosen')->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [DosenResourceController::class, 'index'])->name('dashboard');
    Route::post('/tugas', [DosenResourceController::class, 'storeTugas'])->name('tugas.store');
    Route::put('/tugas/{id}', [DosenResourceController::class, 'updateTugas'])->name('tugas.update');
    Route::delete('/tugas/{id}', [DosenResourceController::class, 'destroyTugas'])->name('tugas.destroy');
    Route::get('/notifikasi/{id}/read', [DosenResourceController::class, 'markNotifikasiRead'])->name('notifikasi.read');
    Route::post('/logout', [DashboardController::class, 'logoutDosen'])->name('logout');
});

Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
    ->name('siswa.dashboard')
    ->middleware('siswa');

Route::get('/siswa/onboarding', [DashboardController::class, 'onboardingShow'])
    ->name('siswa.onboarding.show')
    ->middleware('siswa');

Route::post('/siswa/onboarding/complete', [DashboardController::class, 'onboardingComplete'])
    ->name('siswa.onboarding.complete')
    ->middleware('siswa');

Route::post('/siswa/preferences', [DashboardController::class, 'savePreferences'])
    ->name('siswa.preferences')
    ->middleware('siswa');

Route::post('/siswa/logout', [DashboardController::class, 'logoutSiswa'])
    ->name('siswa.logout')
    ->middleware('siswa');

// Prodi Routes
Route::middleware('prodi')->prefix('prodi')->name('prodi.')->group(function () {
    Route::get('/dashboard', [ProdiDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [DashboardController::class, 'logoutProdi'])->name('logout');
});
