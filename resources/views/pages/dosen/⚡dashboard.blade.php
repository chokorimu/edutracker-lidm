@php use App\Services\BebanCalculator; @endphp
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Dosen</h1>
                <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ $user->name }}</p>
            </div>
            <form method="POST" action="{{ route('dosen.logout') }}">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm">Logout</button>
            </form>
        </div>

        {{-- Flash status --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-md mb-4 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Tab Navigation --}}
        <div class="flex space-x-1 border-b border-gray-200 mb-6">
            @php $tabs = ['tugas' => 'Tugas', 'beban' => 'Beban', 'notifikasi' => 'Notifikasi', 'profil' => 'Profil']; @endphp
            @foreach ($tabs as $key => $label)
                <a href="{{ route('dosen.dashboard', ['tab' => $key]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-t-md transition {{ $currentTab === $key ? 'bg-white border border-b-0 border-gray-200 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Tab Content --}}

        {{-- ===== TUGAS ===== --}}
        @if ($currentTab === 'tugas')
            <div class="space-y-6">
                {{-- Create Form --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Tambah Tugas Baru</h2>

                    @if ($errors->has('beban_warning'))
                        <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-md mb-4 text-sm">
                            {{ $errors->first('beban_warning') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dosen.tugas.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                            <select name="mata_kuliah_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($data['mataKuliahList'] as $mk)
                                    <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                        {{ $mk->nama }} ({{ $mk->kode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Tugas</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="255"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" rows="3"
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bobot (%)</label>
                                <input type="number" name="bobot" value="{{ old('bobot') }}" min="0" max="100"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deadline</label>
                                <input type="datetime-local" name="deadline" value="{{ old('deadline') }}" required
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="override" value="1" id="override" {{ old('override') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="override" class="text-sm text-gray-600">Tetap simpan walau beban berat/overload</label>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
                                Simpan Tugas
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tasks List --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Daftar Tugas</h2>
                    @if($data['tugasList']->count())
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-left">
                                    <th class="py-2 px-2">Nama</th>
                                    <th class="py-2 px-2">Mata Kuliah</th>
                                    <th class="py-2 px-2">Deadline</th>
                                    <th class="py-2 px-2">Status Beban</th>
                                    <th class="py-2 px-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['tugasList'] as $tugas)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 px-2">{{ $tugas->nama }}</td>
                                        <td class="py-2 px-2">{{ $tugas->mataKuliah?->nama ?? '-' }}</td>
                                        <td class="py-2 px-2">{{ \Carbon\Carbon::parse($tugas->deadline)->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 px-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $tugas->status_beban === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::OVERLOAD ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ $tugas->status_beban }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2">
                                            <form method="POST" action="{{ route('dosen.tugas.destroy', $tugas->id) }}" class="inline"
                                                  onsubmit="return confirm('Hapus tugas ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $data['tugasList']->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada tugas.</p>
                    @endif
                </div>
            </div>

        {{-- ===== BEBAN ===== --}}
        @elseif ($currentTab === 'beban')
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Beban Tugas Mahasiswa</h2>
                    <p class="text-xs text-gray-400 mb-4">Minggu ini: {{ $data['weekStart']->format('d/m/Y') }} – {{ $data['weekEnd']->format('d/m/Y') }}</p>

                    @forelse($data['mataKuliahList'] as $mk)
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <h3 class="font-medium text-sm mb-2">{{ $mk->nama }} ({{ $mk->kode }})</h3>

                            @php
                                // Merge this‑week and next‑week results from the controller.
                                $combined = collect($mk['thisWeek'])->map(function ($s) use ($mk, $data) {
                                    $next = collect($mk['nextWeek'])->firstWhere('siswa_id', $s['siswa_id']);
                                    return [
                                        'nama' => $s['nama_siswa'],
                                        'siswa_id' => $s['siswa_id'], // placeholder, real NIM not needed for load view
                                        'weekCount' => $s['count'],
                                        'weekLoad' => $s['status'],
                                        'nextWeekCount' => $next['count'] ?? 0,
                                        'nextWeekLoad' => $next['status'] ?? \App\Services\BebanCalculator::LIGHT,
                                        'isBimbingan' => in_array($s['siswa_id'], $data['bimbinganIds']),
                                    ];
                                });
                                $students = $combined;
                            @endphp

                            @if($students->count())
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left">
                                            <th class="py-1 px-1">Nama</th>
                                            <th class="py-1 px-1">NIM</th>
                                            <th class="py-1 px-1">Minggu Ini</th>
                                            <th class="py-1 px-1">Minggu Depan</th>
                                            <th class="py-1 px-1">Bimbingan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    @foreach($students as $s)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-1 px-1">{{ $s['nama'] }}</td>
                                                <td class="py-1 px-1">{{ $s['siswa_id'] }}</td>
                                                <td class="py-1 px-1">
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-medium
                                                        {{ $s['weekLoad'] === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-200 text-red-800' : '' }}">
                                                        {{ $s['weekCount'] }} ({{ $s['weekLoad'] }})
                                                    </span>
                                                </td>
                                                <td class="py-1 px-1">
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-medium
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-200 text-red-800' : '' }}">
                                                        {{ $s['nextWeekCount'] }} ({{ $s['nextWeekLoad'] }})
                                                    </span>
                                                </td>
                                                <td class="py-1 px-1">
                                                    @if($s['isBimbingan'])
                                                        <span class="text-blue-600 font-bold">✓</span>
                                                    @else
                                                        <span class="text-gray-300">–</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-400 text-xs">Tidak ada mahasiswa terdaftar.</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Anda belum mengajar mata kuliah apapun.</p>
                    @endforelse
                </div>
            </div>

        {{-- ===== NOTIFIKASI ===== --}}
        @elseif ($currentTab === 'notifikasi')
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Notifikasi</h2>

                    @if($data['notifikasiList']->count())
                        @foreach($data['notifikasiList'] as $notif)
                            <div class="border border-gray-200 rounded-lg p-4 mb-3 {{ $notif->is_read ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-sm">{{ $notif->judul }}</h4>
                                        <p class="text-xs text-gray-600 mt-1">{{ $notif->pesan }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                        @if($notif->tipe === 'beban_tinggi')
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Beban Tinggi</span>
                                        @endif
                                    </div>
                                    @if(!$notif->is_read)
                                        <a href="{{ route('dosen.notifikasi.read', $notif->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-xs font-medium whitespace-nowrap ml-2">
                                            Tandai Dibaca
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-4">
                            {{ $data['notifikasiList']->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada notifikasi.</p>
                    @endif
                </div>
            </div>

        {{-- ===== PROFIL ===== --}}
        @elseif ($currentTab === 'profil')
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Profil Dosen</h2>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Nama</dt>
                            <dd class="font-medium">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-medium">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">NIDN</dt>
                            <dd class="font-medium">{{ $user->nidn ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Fakultas</dt>
                            <dd class="font-medium">{{ $user->fakultas ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Mata Kuliah Diajar</dt>
                            <dd class="font-medium">{{ $data['mataKuliahList']->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Mahasiswa Bimbingan</dt>
                            <dd class="font-medium">{{ $data['bimbinganCount'] }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Mata Kuliah List --}}
                @if($data['mataKuliahList']->count())
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-md font-semibold mb-3">Mata Kuliah Diajar</h3>
                        <ul class="space-y-2">
                            @foreach($data['mataKuliahList'] as $mk)
                                <li class="border border-gray-200 rounded-md px-4 py-2 text-sm flex justify-between">
                                    <span>{{ $mk->nama }} ({{ $mk->kode }})</span>
                                    <span class="text-gray-500">{{ $mk->sks }} SKS</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection
