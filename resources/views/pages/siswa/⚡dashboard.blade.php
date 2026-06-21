<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout()
    {
        Auth::guard('siswa')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }
}; ?>

<div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard Mahasiswa</h1>
            <button wire:click="logout" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Logout
            </button>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p>Halo, {{ Auth::guard('siswa')->user()->name }} 👋</p>
            <p class="text-gray-500 text-sm mt-2">Dashboard mahasiswa masih placeholder.</p>
        </div>
    </div>
</div>