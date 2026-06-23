<?php
use App\Http\Controllers\AdminResourceController;
use App\Http\Controllers\DashboardController;
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
    Route::post('/logout', [DashboardController::class, 'logoutAdmin'])->name('logout');
});

Route::get('/dosen/dashboard', [DashboardController::class, 'dosen'])
    ->name('dosen.dashboard')
    ->middleware('dosen');

Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
    ->name('siswa.dashboard')
    ->middleware('siswa');

Route::post('/dosen/logout', [DashboardController::class, 'logoutDosen'])
    ->name('dosen.logout')
    ->middleware('dosen');

Route::post('/siswa/logout', [DashboardController::class, 'logoutSiswa'])
    ->name('siswa.logout')
    ->middleware('siswa');
