<?php
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/login', 'pages::auth.login')->name('login');

Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
    ->name('admin.dashboard')
    ->middleware('admin');

Route::get('/dosen/dashboard', [DashboardController::class, 'dosen'])
    ->name('dosen.dashboard')
    ->middleware('dosen');

Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
    ->name('siswa.dashboard')
    ->middleware('siswa');

Route::post('/admin/logout', [DashboardController::class, 'logoutAdmin'])
    ->name('admin.logout')
    ->middleware('admin');

Route::post('/dosen/logout', [DashboardController::class, 'logoutDosen'])
    ->name('dosen.logout')
    ->middleware('dosen');

Route::post('/siswa/logout', [DashboardController::class, 'logoutSiswa'])
    ->name('siswa.logout')
    ->middleware('siswa');
