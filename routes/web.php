<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/login', 'pages::auth.login')->name('login');

Route::livewire('/admin/dashboard', 'pages::admin.dashboard')
    ->name('admin.dashboard')
    ->middleware('admin');

Route::livewire('/dosen/dashboard', 'pages::dosen.dashboard')
    ->name('dosen.dashboard')
    ->middleware('dosen');

Route::livewire('/siswa/dashboard', 'pages::siswa.dashboard')
    ->name('siswa.dashboard')
    ->middleware('siswa');