<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $activeTab = 'dosen';

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }
}; ?>

<div class="flex min-h-screen bg-gray-50">

    <aside class="w-64 bg-gray-900 text-gray-200 flex flex-col">
        <div class="p-4 text-xl font-bold text-white border-b border-gray-700">
            edutrack daily Admin
        </div>
        <nav class="flex-1 p-4 space-y-1">
            @foreach ([
                'dosen' => 'Dosen',
                'siswa' => 'Mahasiswa',
                'matakuliah' => 'Mata Kuliah',
                'krs' => 'KRS',
                'dosenpa' => 'Dosen PA',
                'kalender' => 'Kalender Akademik',
                'ipk' => 'IPK History',
                'pengaturan' => 'Pengaturan',
            ] as $key => $label)
                <button
                    wire:click="setTab('{{ $key }}')"
                    class="w-full text-left block px-3 py-2 rounded hover:bg-gray-800 {{ $activeTab === $key ? 'bg-gray-800 text-white' : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-semibold capitalize">{{ $activeTab }}</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ Auth::guard('admin')->user()->name }}</span>
                <button wire:click="logout" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm">
                    Logout
                </button>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if ($activeTab === 'dosen')
                <livewire:admin.dosen-crud />
            @elseif ($activeTab === 'siswa')
                <livewire:admin.siswa-crud />
            @elseif ($activeTab === 'matakuliah')
                <livewire:admin.matakuliah-crud />
            @elseif ($activeTab === 'krs')
                <livewire:admin.krs-crud />
            @elseif ($activeTab === 'dosenpa')
                <livewire:admin.dosenpa-crud />
            @elseif ($activeTab === 'kalender')
                <livewire:admin.kalender-crud />
            @elseif ($activeTab === 'ipk')
                <livewire:admin.ipk-crud />
            @elseif ($activeTab === 'pengaturan')
                <livewire:admin.pengaturan-crud />
            @endif
        </main>
    </div>

</div>
