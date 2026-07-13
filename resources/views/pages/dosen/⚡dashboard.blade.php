@php use App\Services\BebanCalculator; @endphp
@extends('layouts.app')

@section('content')
<style>
    /* Scoped decorative touches — pastel accent line + smooth scrollbar */
    .dash-header { position: relative; }
    .dash-header::after {
        content: '';
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 2px;
        background: linear-gradient(90deg, #FFF2CA, #56EFC5, #82EDEC, #92C9FF, #A29BFE);
        opacity: .6;
    }
    #sidebar ::-webkit-scrollbar, main ::-webkit-scrollbar { width: 7px; height: 7px; }
    #sidebar ::-webkit-scrollbar-track, main ::-webkit-scrollbar-track { background: transparent; }
    #sidebar ::-webkit-scrollbar-thumb, main ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #92C9FF, #A29BFE); border-radius: 999px; }
</style>
@php
    $cardClass = 'bg-white border border-soft-border rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 animate-fade-in-up';
    $mutedClass = 'text-soft-muted';
    $inputClass = 'w-full rounded-xl border border-soft-border bg-white px-3 py-2 text-sm transition-all duration-200 focus:border-pastel-biru focus:outline-none focus:ring-4 focus:ring-pastel-biru/15';
    $btnPrimary = 'rounded-xl bg-gradient-to-r from-pastel-hijau-atas to-pastel-hijau-bawah px-4 py-2 text-sm font-semibold text-soft-dark shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200';
    $btnDanger = 'text-xs text-appleRed hover:underline whitespace-nowrap';

    $navItems = [
        'kelas' => ['label' => 'Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        'beban' => ['label' => 'Beban', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        'notifikasi' => ['label' => 'Notifikasi', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        'profil' => ['label' => 'Profil', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];
    $pageTitle = ['kelas' => 'Kelas Saya', 'beban' => 'Beban Mahasiswa', 'notifikasi' => 'Notifikasi', 'profil' => 'Profil Dosen'];
    $pageDesc = ['kelas' => 'Kelola mata kuliah, tugas, dan penilaian.', 'beban' => 'Pantau beban tugas mahasiswa bimbingan.', 'notifikasi' => 'Lihat notifikasi sistem.', 'profil' => 'Informasi profil dosen.'];
    $unread = isset($data['notifikasiList']) ? $data['notifikasiList']->where('is_read', false)->count() : 0;
@endphp

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-backdrop').classList.toggle('hidden');
    }
</script>

<div class="min-h-screen bg-soft-bg lg:grid lg:grid-cols-[16rem_1fr]">
    {{-- ═══ SIDEBAR ═══ --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-soft-border bg-white/85 backdrop-blur-xl -translate-x-full lg:relative lg:w-auto lg:translate-x-0 transition-transform duration-200">
        {{-- Logo --}}
        <div class="flex h-14 items-center gap-2 border-b border-soft-border px-5 bg-gradient-to-r from-pastel-kuning/25 via-transparent to-transparent">
            <x-title role="dosen"/>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
            @foreach ($navItems as $key => $item)
                <a wire:navigate href="{{ route('dosen.dashboard', ['tab' => $key]) }}"
                   style="animation-delay: {{ $loop->index * 50 }}ms"
                   class="animate-fade-in-up flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-200
                          {{ $currentTab === $key
                              ? 'bg-gradient-to-r from-pastel-biru to-pastel-ungu text-soft-dark font-semibold shadow-sm shadow-pastel-ungu/30'
                              : $mutedClass . ' hover:bg-soft-bg hover:text-soft-dark hover:translate-x-0.5' }}">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                    @if ($key === 'notifikasi' && $unread > 0)
                        <span class="ml-auto rounded-full bg-appleRed px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- User + Logout --}}
        <div class="border-t border-soft-border px-3 py-3">
            <div class="flex items-center gap-3 rounded-xl bg-soft-bg px-3 py-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah text-xs font-bold text-soft-dark ring-4 ring-white">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-soft-dark">{{ $user->name }}</p>
                    <p class="truncate text-[11px] {{ $mutedClass }}">Dosen</p>
                </div>
            </div>
            <form method="POST" action="{{ route('dosen.logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm text-appleRed transition-colors hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- ═══ MAIN ═══ --}}
    <main class="flex flex-1 flex-col overflow-y-auto">
        {{-- Sticky Header --}}
        <header class="dash-header sticky top-0 z-20 flex items-center gap-4 bg-soft-bg/90 px-5 py-4 backdrop-blur-lg lg:px-8">
            <button onclick="toggleSidebar()" class="lg:hidden text-soft-dark">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <div class="min-w-0 flex-1">
                <h1 class="text-lg font-bold tracking-tight text-soft-dark">{{ $pageTitle[$currentTab] ?? 'Dashboard' }}</h1>
                <p class="text-xs {{ $mutedClass }}">{{ $pageDesc[$currentTab] ?? '' }}</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah text-xs font-bold text-soft-dark">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="space-y-6 p-5 pb-12 lg:p-8">
            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

        {{-- ═══ KELAS TAB ═══ --}}
        @if ($currentTab === 'kelas')
            <div class="space-y-6">
                @if (!isset($data['selectedMk']))
                    {{-- Course listing --}}
                    <div>
                        <p class="mb-4 text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Mata Kuliah Anda</p>
                        @if($data['mataKuliahList']->isEmpty())
                            <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Belum ada mata kuliah.</p>
                        @else
                            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($data['mataKuliahList'] as $mk)
                                    <a wire:navigate href="{{ route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mk->id]) }}"
                                       style="animation-delay: {{ $loop->index * 60 }}ms"
                                       class="{{ $cardClass }} p-5 hover:shadow-lg hover:-translate-y-1 hover:border-pastel-hijau-atas">
                                        <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">{{ $mk->kode }}</p>
                                        <p class="mt-2 text-sm font-bold text-soft-dark truncate">{{ $mk->nama }}</p>
                                        <p class="mt-1 text-xs {{ $mutedClass }}">{{ $mk->sks }} SKS · {{ $mk->tugas_count }} tugas</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    @php $mk = $data['selectedMk']; @endphp
                    {{-- Breadcrumb --}}
                    <div class="flex items-center gap-3 text-sm">
                        <a wire:navigate href="{{ route('dosen.dashboard', ['tab' => 'kelas']) }}" class="text-soft-dark/70 hover:text-soft-dark transition">← Semua Kelas</a>
                        <span class="{{ $mutedClass }}">|</span>
                        <span class="font-bold text-soft-dark truncate">{{ $mk->kode }} — {{ $mk->nama }}</span>
                    </div>

                    {{-- Tambah Tugas --}}
                    <div class="{{ $cardClass }} p-5">
                        <h3 class="mb-4 text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Tambah Tugas</h3>

                        @if(session('deadline_suggestions'))
                            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                                <p class="mb-2 font-semibold">Saran deadline alternatif:</p>
                                @foreach(session('deadline_suggestions') as $suggestion)
                                    <button type="button" class="mb-1 mr-2 rounded-full border border-amber-300 px-3 py-1 hover:bg-amber-100 hover:scale-105 transition-all duration-200" data-deadline-suggestion="{{ $suggestion['value'] }}">
                                        {{ $suggestion['label'] }} · {{ $suggestion['count'] }} tugas
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @error('beban_warning')
                            <div class="mb-4 rounded-xl border border-appleOrange/40 bg-orange-50 p-4 text-xs text-orange-800">{{ $message }}</div>
                        @enderror

                        @error('bobot_total')
                            <div class="mb-4 rounded-xl border border-appleRed/40 bg-red-50 p-4 text-xs text-red-800">{{ $message }}</div>
                        @enderror

                        @if(!empty($data['aggregatePreview']) && count($data['aggregatePreview']))
                            <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                                @foreach($data['aggregatePreview'] as $preview)
                                    <div class="rounded-xl border p-4 {{ $preview['color'] }}">
                                        <p class="text-xs font-bold">{{ $preview['nama'] }} ({{ $preview['kode'] }})</p>
                                        <p class="mt-1 text-[11px]">{{ $preview['students'] }} mahasiswa · rata-rata {{ $preview['avg_tasks'] }} tugas</p>
                                        <p class="mt-1 text-[11px]">Minggu: {{ $preview['week_label'] ?? '-' }}</p>
                                        <p class="mt-1 text-[11px] font-bold">Status terberat: {{ $preview['label'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form id="form-tugas" method="POST" action="{{ route('dosen.tugas.store') }}" class="grid gap-4 sm:grid-cols-2" data-preview-form data-preview-url="{{ route('dosen.tugas.preview-beban') }}">
                            @csrf
                            <input type="hidden" name="mata_kuliah_id" value="{{ $mk->id }}" data-preview-course>

                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Nama Tugas</label>
                                <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="255" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Deadline</label>
                                @php
                                    $deadlineValue = old('deadline');
                                    $deadlineDateValue = old('deadline_date');
                                    $deadlineTimeValue = old('deadline_time');
                                    $deadlineHourValue = old('deadline_hour');
                                    $deadlineMinuteValue = old('deadline_minute');
                                    if ($deadlineValue) {
                                        try {
                                            $parsedDeadline = \Carbon\Carbon::parse($deadlineValue);
                                            $deadlineValue = $parsedDeadline->format('Y-m-d H:i:s');
                                            $deadlineDateValue = $deadlineDateValue ?: $parsedDeadline->format('Y-m-d');
                                            $deadlineTimeValue = $deadlineTimeValue ?: $parsedDeadline->format('H:i');
                                            $deadlineHourValue = $deadlineHourValue ?: $parsedDeadline->format('H');
                                            $deadlineMinuteValue = $deadlineMinuteValue ?: $parsedDeadline->format('i');
                                        } catch (\Throwable) {}
                                    } elseif ($deadlineTimeValue) {
                                        [$deadlineHourValue, $deadlineMinuteValue] = array_pad(explode(':', $deadlineTimeValue, 2), 2, null);
                                    }
                                @endphp
                                <input type="hidden" name="deadline" id="deadline" value="{{ $deadlineValue }}" data-preview-deadline>
                                <input type="hidden" name="deadline_time" value="{{ $deadlineTimeValue }}" data-deadline-time>
                                <div class="grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                                    <div>
                                        <span class="mb-1 block text-[11px] {{ $mutedClass }}">Tanggal</span>
                                        <input type="date" name="deadline_date" value="{{ $deadlineDateValue }}" required data-deadline-date class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <span class="mb-1 block text-[11px] {{ $mutedClass }}">Jam</span>
                                        <select name="deadline_hour" required data-deadline-hour class="{{ $inputClass }}">
                                            <option value="">Jam</option>
                                            @for($hour = 0; $hour < 24; $hour++)
                                                @php $hourValue = str_pad((string) $hour, 2, '0', STR_PAD_LEFT); @endphp
                                                <option value="{{ $hourValue }}" @selected($deadlineHourValue === $hourValue)>{{ $hourValue }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <span class="mb-1 block text-[11px] {{ $mutedClass }}">Menit</span>
                                        <select name="deadline_minute" required data-deadline-minute class="{{ $inputClass }}">
                                            <option value="">Menit</option>
                                            @for($minute = 0; $minute < 60; $minute++)
                                                @php $minuteValue = str_pad((string) $minute, 2, '0', STR_PAD_LEFT); @endphp
                                                <option value="{{ $minuteValue }}" @selected($deadlineMinuteValue === $minuteValue)>{{ $minuteValue }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs {{ $mutedClass }}" data-preview-week>Isi deadline untuk estimasi beban.</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Deskripsi</label>
                                <textarea name="deskripsi" rows="2" class="{{ $inputClass }}">{{ old('deskripsi') }}</textarea>
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Bobot Tugas (%) <span class="font-normal normal-case">— kosongkan untuk otomatis</span></label>
                                <input type="number" name="bobot" value="{{ old('bobot') }}" min="0" max="100" step="0.01" placeholder="Otomatis" class="{{ $inputClass }}">
                            </div>

                            {{-- Live preview panel --}}
                            <div class="hidden rounded-2xl border border-soft-border bg-gradient-to-br from-pastel-kuning/10 via-soft-bg to-pastel-biru/10 p-4 sm:col-span-2" data-preview-panel>
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-soft-dark">Preview Beban Mahasiswa</h3>
                                        <p class="mt-1 text-xs {{ $mutedClass }} truncate" data-preview-week>Isi mata kuliah dan deadline untuk melihat estimasi.</p>
                                    </div>
                                    <span class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap" data-preview-status></span>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div class="rounded-xl border border-soft-border bg-white p-3">
                                        <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Mahasiswa terdampak</p>
                                        <p class="mt-1 text-xl font-bold text-soft-dark" data-preview-students>0</p>
                                    </div>
                                    <div class="rounded-xl border border-soft-border bg-white p-3">
                                        <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Rata-rata tugas</p>
                                        <p class="mt-1 text-xl font-bold text-soft-dark" data-preview-average>0</p>
                                    </div>
                                    <div class="rounded-xl border border-soft-border bg-white p-3">
                                        <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Status terberat</p>
                                        <p class="mt-1 text-xl font-bold text-soft-dark" data-preview-label>-</p>
                                    </div>
                                </div>
                                <div class="mt-4 hidden rounded-xl border border-appleRed/40 bg-red-50 p-3 text-sm text-red-800" data-preview-warning>
                                    <p class="font-semibold">Peringatan: pekan deadline ini sudah padat.</p>
                                    <p class="mt-1 text-xs">Pilih salah satu saran deadline di bawah atau centang override untuk tetap menyimpan.</p>
                                </div>
                                <div class="mt-4 hidden" data-preview-suggestions-wrap>
                                    <p class="mb-2 text-xs font-semibold text-soft-dark">Saran reschedule</p>
                                    <div class="flex flex-wrap gap-2" data-preview-suggestions></div>
                                </div>
                                <div class="mt-4 overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="border-b border-soft-border text-left {{ $mutedClass }}">
                                                <th class="py-2 pr-2">Mahasiswa</th>
                                                <th class="px-2 py-2">Saat ini</th>
                                                <th class="px-2 py-2">Jika disimpan</th>
                                                <th class="py-2 pl-2">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody data-preview-student-rows></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="rounded-xl border border-appleOrange/40 bg-orange-50 p-4 sm:col-span-2">
                                <label class="flex cursor-pointer items-start gap-2 text-xs text-orange-900">
                                    <input type="checkbox" name="override" value="1" @checked(old('override')) class="mt-0.5 rounded border-orange-300 text-appleOrange focus:ring-appleOrange">
                                    <span>
                                        <span class="block font-semibold">Tetap lanjut dengan override</span>
                                        <span class="mt-0.5 block text-orange-800/80">Gunakan saat preview menampilkan warning beban atau kelas masih memiliki submit parsial.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="sm:col-span-2">
                                <button type="submit" class="{{ $btnPrimary }} w-full sm:w-auto">Simpan Tugas</button>
                            </div>
                        </form>
                    </div>

                    {{-- Daftar Tugas & Nilai --}}
                    <div class="{{ $cardClass }} overflow-hidden">
                        <div class="border-b border-soft-border px-5 py-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Daftar Tugas & Nilai</h3>
                        </div>

                        @if($data['tugasList']->isEmpty())
                            <p class="px-5 py-4 text-sm {{ $mutedClass }}">Belum ada tugas di kelas ini.</p>
                        @else
                            @foreach($data['tugasList'] as $tugas)
                                <div class="border-b border-soft-border/70 px-5 py-4 last:border-0">
                                    <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-soft-dark truncate">{{ $tugas->nama }}</p>
                                            <p class="text-xs {{ $mutedClass }} flex flex-wrap items-center gap-1">
                                                <span>Deadline: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d/m/Y H:i') }}</span>
                                                <span>· Bobot: {{ $tugas->bobot }}%</span>
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-soft-border
                                                    {{ $tugas->status_beban === BebanCalculator::LIGHT ? 'bg-green-50 text-appleGreen' : '' }}
                                                    {{ $tugas->status_beban === BebanCalculator::NORMAL ? 'bg-amber-50 text-appleOrange' : '' }}
                                                    {{ $tugas->status_beban === BebanCalculator::HEAVY ? 'bg-red-50 text-appleRed' : '' }}
                                                    {{ $tugas->status_beban === BebanCalculator::OVERLOAD ? 'bg-red-100 text-appleRed' : '' }}">
                                                    {{ $tugas->status_beban }}
                                                </span>
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('dosen.tugas.destroy', $tugas->id) }}" onsubmit="return confirm('Hapus tugas ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                                        </form>
                                    </div>

                                    @if($data['siswaList']->isEmpty())
                                        <p class="text-xs {{ $mutedClass }}">Tidak ada mahasiswa terdaftar di KRS.</p>
                                    @else
                                        <details class="group mt-3 rounded-xl border border-soft-border bg-soft-bg overflow-hidden">
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 hover:bg-soft-bg font-semibold text-xs text-soft-dark select-none">
                                                <span>Lihat Status Pengumpulan & Nilai Mahasiswa ({{ $data['siswaList']->count() }})</span>
                                                <svg class="h-4 w-4 flex-shrink-0 {{ $mutedClass }} transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </summary>
                                            <div class="border-t border-soft-border">
                                                <div class="overflow-x-auto px-4 py-3">
                                                    <table class="w-full text-xs min-w-[600px]">
                                                        <thead>
                                                            <tr class="text-left {{ $mutedClass }}">
                                                                <th class="w-1/4 pb-2 font-medium">Mahasiswa</th>
                                                                <th class="w-1/5 pb-2 font-medium">NIM</th>
                                                                <th class="pb-2 font-medium">Nilai (0-100)</th>
                                                                <th class="pb-2 font-medium">Komentar</th>
                                                                <th></th>
                                                                <th class="pb-2 font-medium">File Submission</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($data['siswaList'] as $krs)
                                                                @php
                                                                    $existing = $data['nilaiMap'][$tugas->id][$krs->siswa_id] ?? null;
                                                                    $submission = $data['submissionMap'][$tugas->id][$krs->siswa_id] ?? null;
                                                                @endphp
                                                                <tr class="border-t border-soft-border/50 hover:bg-soft-bg/60 transition-colors">
                                                                    <td class="py-1.5 pr-2 font-medium text-soft-dark whitespace-nowrap">{{ $krs->siswa->name }}</td>
                                                                    <td class="py-1.5 pr-2 {{ $mutedClass }} whitespace-nowrap">{{ $krs->siswa->nim }}</td>
                                                                    <td class="py-1.5 pr-2" colspan="3">
                                                                        <form method="POST" action="{{ route('dosen.nilai.store', [$tugas->id, $krs->siswa_id]) }}" class="flex items-center gap-2">
                                                                            @csrf
                                                                            <input type="number" name="nilai" min="0" max="100" step="0.01" value="{{ $existing?->nilai }}" placeholder="-" class="w-20 min-w-[5rem] rounded-lg border border-soft-border px-2 py-1 text-xs focus:border-pastel-biru focus:outline-none">
                                                                            <input type="text" name="komentar" value="{{ $existing?->komentar }}" placeholder="Komentar (opsional)" class="flex-1 min-w-[8rem] rounded-lg border border-soft-border px-2 py-1 text-xs focus:border-pastel-biru focus:outline-none">
                                                                            <button type="submit" class="rounded-lg bg-gradient-to-r from-pastel-biru to-pastel-ungu px-2 py-1 text-[10px] font-medium text-soft-dark shadow-sm hover:shadow-md hover:opacity-95 transition-all duration-200 whitespace-nowrap">
                                                                                {{ $existing ? 'Update' : 'Simpan' }}
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                    <td class="py-1.5 pr-2">
                                                                        @if($submission)
                                                                            <div class="flex max-w-[12rem] flex-col gap-0.5">
                                                                                <a href="{{ route('dosen.submission.download', $submission->id) }}" class="truncate text-[10px] font-medium text-soft-dark hover:underline" title="{{ $submission->file_name }}">
                                                                                    Download {{ $submission->file_name }}
                                                                                </a>
                                                                                <span class="text-[10px] {{ $submission->status === 'late' ? 'font-bold text-appleOrange' : $mutedClass }} whitespace-nowrap">
                                                                                    {{ $submission->status === 'late' ? 'Terlambat' : 'Tepat waktu' }}
                                                                                    · {{ \Carbon\Carbon::parse($submission->submitted_at)->translatedFormat('d M H:i') }}
                                                                                </span>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-[10px] font-bold text-appleRed whitespace-nowrap">Belum submit</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- JS for deadline sync + live preview --}}
                    <script>
                        (() => {
                            const form = document.querySelector("[data-preview-form]");
                            const deadlineInput = document.getElementById("deadline");
                            const deadlineDateInput = document.querySelector("[data-deadline-date]");
                            const deadlineTimeInput = document.querySelector("[data-deadline-time]");
                            const deadlineHourInput = document.querySelector("[data-deadline-hour]");
                            const deadlineMinuteInput = document.querySelector("[data-deadline-minute]");

                            const splitDeadline = (value) => {
                                const normalized = value.replace(" ", "T");
                                const [datePart, timePart = ""] = normalized.split("T");
                                return { date: datePart || "", time: timePart.slice(0, 5) };
                            };

                            const syncDeadline = () => {
                                if (!deadlineInput || !deadlineDateInput || !deadlineTimeInput || !deadlineHourInput || !deadlineMinuteInput) return;
                                deadlineTimeInput.value = deadlineHourInput.value && deadlineMinuteInput.value ? `${deadlineHourInput.value}:${deadlineMinuteInput.value}` : "";
                                deadlineInput.value = deadlineDateInput.value && deadlineTimeInput.value ? `${deadlineDateInput.value} ${deadlineTimeInput.value}:00` : "";
                                deadlineInput.dispatchEvent(new Event("input", { bubbles: true }));
                            };

                            document.querySelectorAll("[data-deadline-suggestion]").forEach((button) => {
                                button.addEventListener("click", () => {
                                    if (deadlineInput) {
                                        const parts = splitDeadline(button.dataset.deadlineSuggestion);
                                        deadlineDateInput.value = parts.date;
                                        deadlineHourInput.value = parts.time.slice(0, 2);
                                        deadlineMinuteInput.value = parts.time.slice(3, 5);
                                        syncDeadline();
                                    }
                                });
                            });

                            if (!form) return;

                            const courseInput = form.querySelector("[data-preview-course]");
                            const previewDeadline = form.querySelector("[data-preview-deadline]");
                            const panel = form.querySelector("[data-preview-panel]");
                            const statusBadge = form.querySelector("[data-preview-status]");
                            const weekLabel = form.querySelector("[data-preview-week]");
                            const studentsCount = form.querySelector("[data-preview-students]");
                            const averageCount = form.querySelector("[data-preview-average]");
                            const summaryLabel = form.querySelector("[data-preview-label]");
                            const warning = form.querySelector("[data-preview-warning]");
                            const suggestionsWrap = form.querySelector("[data-preview-suggestions-wrap]");
                            const suggestions = form.querySelector("[data-preview-suggestions]");
                            const studentRows = form.querySelector("[data-preview-student-rows]");
                            const token = form.querySelector('input[name="_token"]')?.value;
                            let abortController = null;
                            let debounce = null;

                            const setBadgeClass = (el, color) => { el.className = `inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-semibold ${color}`; };

                            const renderSuggestions = (items) => {
                                suggestions.innerHTML = "";
                                suggestionsWrap.classList.toggle("hidden", items.length === 0);
                                items.forEach((item) => {
                                    const btn = document.createElement("button");
                                    btn.type = "button";
                                    btn.className = `rounded-full border px-3 py-1 text-xs font-semibold hover:opacity-80 ${item.color}`;
                                    btn.textContent = `${item.label} · ${item.count} tugas · ${item.label_status}`;
                                    btn.addEventListener("click", () => {
                                        const parts = splitDeadline(item.value);
                                        deadlineDateInput.value = parts.date;
                                        deadlineHourInput.value = parts.time.slice(0, 2);
                                        deadlineMinuteInput.value = parts.time.slice(3, 5);
                                        syncDeadline();
                                    });
                                    suggestions.appendChild(btn);
                                });
                            };

                            const renderStudents = (items) => {
                                studentRows.innerHTML = "";
                                if (items.length === 0) {
                                    const row = document.createElement("tr");
                                    const cell = document.createElement("td");
                                    cell.colSpan = 4;
                                    cell.className = "py-3 text-center text-soft-muted";
                                    cell.textContent = "Belum ada mahasiswa KRS pada mata kuliah ini.";
                                    row.appendChild(cell);
                                    studentRows.appendChild(row);
                                    return;
                                }
                                items.forEach((student) => {
                                    const row = document.createElement("tr");
                                    row.className = "border-b border-soft-border/50 last:border-0";
                                    const identity = document.createElement("td");
                                    identity.className = "py-2 pr-2";
                                    const name = document.createElement("span");
                                    name.className = "font-medium text-soft-dark";
                                    name.textContent = student.nama;
                                    const nim = document.createElement("span");
                                    nim.className = "block text-[11px] text-soft-muted";
                                    nim.textContent = student.nim;
                                    identity.append(name, nim);
                                    const current = document.createElement("td");
                                    current.className = "py-2 px-2 text-soft-muted";
                                    current.textContent = `${student.current_count} tugas`;
                                    const projected = document.createElement("td");
                                    projected.className = "py-2 px-2 font-semibold text-soft-dark";
                                    projected.textContent = `${student.projected_count} tugas`;
                                    const status = document.createElement("td");
                                    status.className = "py-2 pl-2";
                                    const badge = document.createElement("span");
                                    badge.className = `inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold ${student.color}`;
                                    badge.textContent = student.label;
                                    status.appendChild(badge);
                                    row.append(identity, current, projected, status);
                                    studentRows.appendChild(row);
                                });
                            };

                            const renderPreview = (payload) => {
                                panel.classList.remove("hidden");
                                weekLabel.textContent = `${payload.course.nama} (${payload.course.kode}) · ${payload.week.label}`;
                                studentsCount.textContent = payload.summary.students;
                                averageCount.textContent = payload.summary.avg_tasks;
                                summaryLabel.textContent = payload.summary.label;
                                statusBadge.textContent = payload.summary.label;
                                setBadgeClass(statusBadge, payload.summary.color);
                                warning.classList.toggle("hidden", !payload.summary.needs_warning);
                                renderSuggestions(payload.suggestions || []);
                                renderStudents(payload.students || []);
                            };

                            const fetchPreview = () => {
                                const mataKuliahId = courseInput.value;
                                const deadline = previewDeadline.value;
                                if (!mataKuliahId || !deadline) { panel.classList.add("hidden"); return; }
                                abortController?.abort();
                                abortController = new AbortController();
                                fetch(form.dataset.previewUrl, {
                                    method: "POST",
                                    headers: { "Accept": "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": token },
                                    body: JSON.stringify({ mata_kuliah_id: mataKuliahId, deadline }),
                                    signal: abortController.signal,
                                })
                                    .then((r) => r.ok ? r.json() : Promise.reject(r))
                                    .then(renderPreview)
                                    .catch((e) => { if (e.name !== "AbortError") panel.classList.add("hidden"); });
                            };

                            const schedulePreview = () => { clearTimeout(debounce); debounce = setTimeout(fetchPreview, 250); };
                            deadlineDateInput?.addEventListener("input", syncDeadline);
                            deadlineHourInput?.addEventListener("change", syncDeadline);
                            deadlineMinuteInput?.addEventListener("change", syncDeadline);
                            previewDeadline.addEventListener("input", schedulePreview);
                            syncDeadline();
                            schedulePreview();
                        })();
                    </script>
                @endif
            </div>

        {{-- ═══ BEBAN TAB ═══ --}}
        @elseif ($currentTab === 'beban')
            <div class="space-y-6">
                <div class="{{ $cardClass }} p-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Beban Tugas Mahasiswa</p>
                    <p class="mt-1 text-xs {{ $mutedClass }}">Minggu ini: {{ $data['weekStart']->format('d/m/Y') }} – {{ $data['weekEnd']->format('d/m/Y') }}</p>

                    @if(count($data['paRiskCards']))
                        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($data['paRiskCards'] as $student)
                                <div class="rounded-xl border p-4 {{ $student['color'] }}">
                                    <div class="flex justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold truncate">{{ $student['nama'] }}</h3>
                                            <p class="text-[11px] opacity-80 truncate">{{ $student['nim'] }}</p>
                                        </div>
                                        <span class="text-lg font-bold whitespace-nowrap">{{ $student['risk_score'] }}%</span>
                                    </div>
                                    <p class="text-xs mt-2">{{ $student['task_count'] }} tugas minggu ini · {{ $student['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($data['mataKuliahList']->count() > 1)
                        <div class="mt-5">
                            <label class="block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-1">Pilih Mata Kuliah</label>
                            <select onchange="window.location.href='{{ route('dosen.dashboard') }}?tab=beban&mk_beban=' + this.value" class="{{ $inputClass }} max-w-xs">
                                @foreach($data['mataKuliahList'] as $mkItem)
                                    <option value="{{ $mkItem->id }}" {{ $data['selectedBebanMkId'] == $mkItem->id ? 'selected' : '' }}>
                                        {{ $mkItem->kode }} — {{ $mkItem->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @forelse($data['workloadData'] as $mkData)
                        <div class="mt-5 rounded-xl border border-soft-border bg-soft-bg p-4">
                            <h3 class="font-bold text-sm text-soft-dark mb-3 truncate">{{ $mkData['nama'] }} ({{ $mkData['kode'] }})</h3>

                            @php
                                $combined = collect($mkData['thisWeek'])->map(function ($s) use ($mkData, $data) {
                                    $next = collect($mkData['nextWeek'])->firstWhere('siswa_id', $s['siswa_id']);
                                    return [
                                        'nama' => $s['nama_siswa'],
                                        'siswa_id' => $s['siswa_id'],
                                        'nim' => $s['nim'],
                                        'weekCount' => $s['count'],
                                        'weekLoad' => $s['status'],
                                        'nextWeekCount' => $next['count'] ?? 0,
                                        'nextWeekLoad' => $next['status'] ?? BebanCalculator::LIGHT,
                                        'isBimbingan' => in_array($s['siswa_id'], $data['bimbinganIds']),
                                    ];
                                });
                                $students = $combined;
                            @endphp

                            @if($students->count())
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs min-w-[500px]">
                                        <thead>
                                            <tr class="border-b border-soft-border text-left {{ $mutedClass }}">
                                                <th class="py-2 px-1">Nama</th>
                                                <th class="py-2 px-1">NIM</th>
                                                <th class="py-2 px-1">Minggu Ini</th>
                                                <th class="py-2 px-1">Minggu Depan</th>
                                                <th class="py-2 px-1">Bimbingan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $s)
                                                <tr class="border-b border-soft-border/50 hover:bg-soft-bg/60 transition-colors">
                                                    <td class="py-1.5 px-1 whitespace-nowrap text-soft-dark">{{ $s['nama'] }}</td>
                                                    <td class="py-1.5 px-1 whitespace-nowrap {{ $mutedClass }}">{{ $s['nim'] ?? $s['siswa_id'] }}</td>
                                                    <td class="py-1.5 px-1">
                                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-bold ring-1 ring-soft-border
                                                            {{ $s['weekLoad'] === BebanCalculator::LIGHT ? 'bg-green-50 text-appleGreen' : '' }}
                                                            {{ $s['weekLoad'] === BebanCalculator::NORMAL ? 'bg-amber-50 text-appleOrange' : '' }}
                                                            {{ $s['weekLoad'] === BebanCalculator::HEAVY ? 'bg-red-50 text-appleRed' : '' }}
                                                            {{ $s['weekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-100 text-appleRed' : '' }}">
                                                            {{ $s['weekCount'] }} ({{ $s['weekLoad'] }})
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-1">
                                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-bold ring-1 ring-soft-border
                                                            {{ $s['nextWeekLoad'] === BebanCalculator::LIGHT ? 'bg-green-50 text-appleGreen' : '' }}
                                                            {{ $s['nextWeekLoad'] === BebanCalculator::NORMAL ? 'bg-amber-50 text-appleOrange' : '' }}
                                                            {{ $s['nextWeekLoad'] === BebanCalculator::HEAVY ? 'bg-red-50 text-appleRed' : '' }}
                                                            {{ $s['nextWeekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-100 text-appleRed' : '' }}">
                                                            {{ $s['nextWeekCount'] }} ({{ $s['nextWeekLoad'] }})
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-1 text-center">
                                                        @if($s['isBimbingan'])
                                                            <span class="text-appleGreen font-bold">✓</span>
                                                        @else
                                                            <span class="{{ $mutedClass }}">–</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Tidak ada mahasiswa terdaftar.</p>
                            @endif
                        </div>
                    @empty
                        <p class="mt-4 rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Anda belum mengajar mata kuliah apapun.</p>
                    @endforelse
                </div>
            </div>

        {{-- ═══ NOTIFIKASI TAB ═══ --}}
        @elseif ($currentTab === 'notifikasi')
            <div class="space-y-4">
                <div class="{{ $cardClass }} divide-y divide-soft-border overflow-hidden">
                    @if($data['notifikasiList']->count())
                        @foreach($data['notifikasiList'] as $notif)
                            <div class="px-5 py-4 hover:bg-soft-bg/70 transition-colors duration-200 {{ !$notif->is_read ? 'border-l-4 border-pastel-ungu bg-pastel-ungu/5' : '' }}">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-soft-dark truncate">{{ $notif->judul }}</h4>
                                            @if(!$notif->is_read)
                                                <span class="h-2 w-2 rounded-full bg-appleRed animate-pulse flex-shrink-0"></span>
                                            @endif
                                        </div>
                                        <p class="text-xs {{ $mutedClass }} mt-1">{{ $notif->pesan }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] font-mono {{ $mutedClass }}">{{ $notif->created_at->diffForHumans() }}</span>
                                            @if($notif->tipe === 'beban_tinggi')
                                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-appleRed ring-1 ring-red-100">Beban Tinggi</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$notif->is_read)
                                        <form method="POST" action="{{ route('dosen.notifikasi.read', $notif->id) }}" class="flex-shrink-0">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-soft-dark hover:underline whitespace-nowrap">Tandai Dibaca</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="px-5 py-8">
                            <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Belum ada notifikasi.</p>
                        </div>
                    @endif
                </div>
                @if($data['notifikasiList']->count())
                    <div>{{ $data['notifikasiList']->links() }}</div>
                @endif
            </div>

        {{-- ═══ PROFIL TAB ═══ --}}
        @elseif ($currentTab === 'profil')
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-[.85fr_1.35fr]">
                    {{-- Identity card --}}
                    <div class="{{ $cardClass }} p-6 text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah text-2xl font-bold text-soft-dark ring-4 ring-soft-bg shadow-lg shadow-pastel-hijau-atas/30">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h2 class="mt-4 text-lg font-bold text-soft-dark">{{ $user->name }}</h2>
                        <p class="text-sm {{ $mutedClass }}">{{ $user->nidn ?? '-' }}</p>
                        <div class="mt-4 space-y-2 text-left">
                            <div class="flex items-center justify-between border-t border-soft-border pt-2">
                                <span class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Email</span>
                                <span class="text-sm text-soft-dark truncate ml-2">{{ $user->email }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-soft-border pt-2">
                                <span class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Fakultas</span>
                                <span class="text-sm text-soft-dark">{{ $user->fakultas ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="space-y-6">
                        <div class="{{ $cardClass }} p-5">
                            <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-4">Ringkasan</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-xl bg-soft-bg p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Mata Kuliah</p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight text-soft-dark">{{ $data['mataKuliahList']->count() }}</p>
                                </div>
                                <div class="rounded-xl bg-soft-bg p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Mahasiswa Bimbingan</p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight text-soft-dark">{{ $data['bimbinganCount'] }}</p>
                                </div>
                            </div>
                        </div>

                        @if($data['mataKuliahList']->count())
                            <div class="{{ $cardClass }} p-5">
                                <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-4">Mata Kuliah Diajar</p>
                                <div class="space-y-2">
                                    @foreach($data['mataKuliahList'] as $mkItem)
                                        <div class="flex items-center justify-between rounded-xl border border-soft-border px-4 py-3">
                                            <span class="text-sm font-semibold text-soft-dark truncate min-w-0">{{ $mkItem->nama }} ({{ $mkItem->kode }})</span>
                                            <span class="text-xs {{ $mutedClass }} whitespace-nowrap ml-2">{{ $mkItem->sks }} SKS</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status Akun --}}
                <div class="{{ $cardClass }} p-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-3">Status Akun</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between border-b border-soft-border pb-2">
                            <span class="{{ $mutedClass }}">Role</span>
                            <span class="font-semibold text-soft-dark">Dosen</span>
                        </div>
                        <div class="flex justify-between border-b border-soft-border pb-2">
                            <span class="{{ $mutedClass }}">Status</span>
                            <span class="font-semibold text-appleGreen">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        </div>
    </main>
</div>
@endsection