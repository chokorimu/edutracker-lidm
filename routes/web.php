<?php

use App\Http\Controllers\AdminResourceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenResourceController;
use App\Http\Controllers\ProdiDashboardController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Volt::route('/login', 'pages::auth.login')->name('login');

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
    Route::post('/tugas/preview-beban', [DosenResourceController::class, 'previewBeban'])->name('tugas.preview-beban');
    Route::post('/tugas', [DosenResourceController::class, 'storeTugas'])->name('tugas.store');
    Route::put('/tugas/{id}', [DosenResourceController::class, 'updateTugas'])->name('tugas.update');
    Route::delete('/tugas/{id}', [DosenResourceController::class, 'destroyTugas'])->name('tugas.destroy');
    Route::post('/nilai/{tugasId}/{siswaId}', [DosenResourceController::class, 'storeNilai'])->name('nilai.store');
    Route::get('/submission/{submissionId}/download', [DosenResourceController::class, 'downloadSubmission'])->name('submission.download');
    Route::patch('/notifikasi/{id}/read', [DosenResourceController::class, 'markNotifikasiRead'])->name('notifikasi.read');
    Route::post('/logout', [DashboardController::class, 'logoutDosen'])->name('logout');
});

Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
    ->name('siswa.dashboard')
    ->middleware('siswa');

Route::post('/siswa/tugas/{tugasId}/submit', [DashboardController::class, 'submitTugas'])
    ->name('siswa.tugas.submit')
    ->middleware('siswa');

Route::get('/siswa/tugas/{tugasId}/submission', [DashboardController::class, 'downloadSubmission'])
    ->name('siswa.submission.download')
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
