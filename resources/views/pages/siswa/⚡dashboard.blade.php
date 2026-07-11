@php
    use App\Services\BebanCalculator;
    use Carbon\Carbon;

    $profile = $data['profile'] ?? [];

    // HITUNG IPK KUMULATIF SEBAGAI MEAN DARI SEMUA SEMESTER
    $ipkHistory = collect($data['ipk_history'] ?? []);
    $ipkKumulatif = $ipkHistory->isNotEmpty() 
        ? number_format($ipkHistory->avg('ipk'), 2) 
        : ($profile['ipk'] ?? '0.00');

    // CAPTION DINAMIS BERDASARKAN JUMLAH SEMESTER
    $semesterCount = $ipkHistory->count();
    $ipkCaption = $semesterCount > 1 
        ? "Rata-rata dari {$semesterCount} semester" 
        : ($semesterCount === 1 ? 'Semester terakhir' : 'Belum ada data');

    $initials = strtoupper(substr($profile['nama'] ?? '-', 0, 2));
    $status = $profile['weekly_status'] ?? BebanCalculator::LIGHT;

    $pageMeta = [
        'dashboard'     => ['title' => 'Dashboard Akademik', 'desc' => 'Ringkasan beban tugas, SKS, dan deadline terdekat.'],
        'calendar'      => ['title' => 'Kalender Akademik', 'desc' => 'Peta deadline tugas per tanggal.'],
        'monitoring'    => ['title' => 'Monitoring SKS', 'desc' => 'Distribusi SKS dan beban mata kuliah semester ini.'],
        'analytics'     => ['title' => 'Analitik Akademik', 'desc' => 'Performa, risiko, dan rekomendasi akademik.'],
        'notifications' => ['title' => 'Notifikasi', 'desc' => 'Pemberitahuan penting terkait aktivitas akademik.'],
        'tugas'         => ['title' => 'Submit Tugas', 'desc' => 'Upload dan pantau status pengumpulan tugas.'],
        'profile'       => ['title' => 'Profil Mahasiswa', 'desc' => 'Identitas dan ringkasan akademik mahasiswa.'],
    ];

    $navItems = [
        'dashboard'     => ['label' => 'Dashboard', 'icon' => 'M4 6h6v6H4V6Zm10 0h6v6h-6V6ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z'],
        'calendar'      => ['label' => 'Kalender', 'icon' => 'M8 3v4m8-4v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z'],
        'monitoring'    => ['label' => 'Monitoring SKS', 'icon' => 'M5 19V9m7 10V5m7 14v-7M3 19h18'],
        'analytics'     => ['label' => 'Analitik', 'icon' => 'M4 19V5m0 14h16M7 15l3-3 3 2 5-7'],
        'notifications' => ['label' => 'Notifikasi', 'icon' => 'M15 17h5l-1.5-2V11a6.5 6.5 0 0 0-13 0v4L4 17h5m6 0a3 3 0 0 1-6 0'],
        'tugas'         => ['label' => 'Tugas', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'profile'       => ['label' => 'Profil', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0'],
    ];

    $pageTitle = $pageMeta[$currentTab]['title'] ?? $pageMeta['dashboard']['title'];
    $pageDescription = $pageMeta[$currentTab]['desc'] ?? $pageMeta['dashboard']['desc'];

    $cardClass = 'bg-white border border-soft-border rounded-2xl shadow-sm transition-all duration-300 animate-fade-in-up';
    $mutedClass = 'text-soft-muted';
@endphp

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-soft-bg text-soft-dark font-sans selection:bg-soft-dark selection:text-white antialiased">
    <div class="grid min-h-screen lg:grid-cols-[16rem_1fr]">
        {{-- SIDEBAR --}}
        <aside class="border-b border-soft-border bg-white/85 backdrop-blur lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between gap-4 px-5 py-4 lg:block lg:px-6 lg:py-6">
                <x-title role="siswa"/>
                <form method="POST" action="{{ route('siswa.logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-bold text-appleRed hover:bg-red-50 transition">Keluar</button>
                </form>
            </div>

            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:flex-col lg:overflow-visible lg:px-4 lg:pb-0">
                @foreach($navItems as $tab => $item)
                    <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => $tab]) }}"
                       @class([
                           'flex shrink-0 items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200',
                           'bg-gradient-to-r from-pastel-biru to-pastel-ungu text-soft-dark shadow-md' => $currentTab === $tab,
                           'text-soft-muted hover:bg-soft-bg hover:text-soft-dark' => $currentTab !== $tab,
                       ])>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                            <path d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto hidden p-4 lg:block">
                <div class="mb-4 rounded-2xl bg-soft-bg p-4 ring-1 ring-soft-border">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah text-xs font-bold text-soft-dark ring-4 ring-white">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">{{ $profile['nama'] ?? 'Mahasiswa' }}</p>
                            <p class="text-[11px] {{ $mutedClass }}">{{ $profile['nim'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('siswa.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-xl border border-red-100 px-3 py-2 text-sm font-bold text-appleRed hover:bg-red-50 transition">Keluar Sesi</button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="min-w-0">
            <header class="sticky top-0 z-10 border-b border-soft-border bg-soft-bg/90 px-5 py-4 backdrop-blur lg:px-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ $pageTitle }}</h1>
                        <p class="mt-1 text-sm {{ $mutedClass }}">{{ $pageDescription }}</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-white px-3 py-2 shadow-sm ring-1 ring-soft-border">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah text-xs font-bold text-soft-dark">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold">{{ $profile['nama'] ?? 'Mahasiswa' }}</p>
                            <p class="text-[10px] {{ $mutedClass }}">Semester {{ $profile['semester'] ?? '-' }} · {{ $profile['prodi'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-6 p-5 pb-12 lg:p-8">
                
                {{-- DASHBOARD TAB --}}
                @if($currentTab === 'dashboard')
                    @if(in_array($status, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true))
                        <section class="rounded-2xl border border-pastel-kuning bg-gradient-to-r from-pastel-kuning to-white p-4 shadow-sm">
                            <div class="flex gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-pastel-ungu shadow-sm ring-1 ring-pastel-kuning">!</div>
                                <div>
                                    <h2 class="text-sm font-bold">Beban Akademik {{ $status === BebanCalculator::OVERLOAD ? 'Overload' : 'Tinggi' }}</h2>
                                    <p class="mt-1 text-xs text-soft-dark/70">Prioritaskan deadline terdekat dan kurangi penumpukan tugas minggu ini.</p>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['Total SKS Aktif', $profile['sks_semester'] ?? 0, 'text-soft-dark', 'SKS semester ini'],
                            ['Tugas On Going', $data['weekly_task_count'] ?? 0, 'text-soft-dark', $data['workload_week_label'] ?? 'deadline aktif'],
                            ['Deadline 3 Hari', $data['deadline_terdekat'] ?? 0, 'text-pastel-ungu', 'perlu dipantau'],
                            ['Status Beban', $data['status_beban_label'] ?? '-', $data['status_beban_color'] ?? 'text-soft-dark', $data['workload_week_label'] ?? 'pekan berjalan'],
                        ] as [$label, $value, $color, $caption])
                            <div class="{{ $cardClass }} p-5 hover:shadow-lg hover:-translate-y-1 hover:border-pastel-biru">
                                <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">{{ $label }}</p>
                                <p class="mt-3 text-3xl font-bold tracking-tight {{ $color }}">{{ $value }}</p>
                                <p class="mt-1 text-[11px] {{ $mutedClass }}">{{ $caption }}</p>
                            </div>
                        @endforeach
                    </section>

                    <section class="grid gap-6 xl:grid-cols-[1.4fr_.9fr]">
                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-bold">Distribusi Beban Mingguan</h2>
                                    <p class="mt-1 text-xs {{ $mutedClass }}">{{ $data['workload_week_label'] ?? 'Pekan berjalan' }} · jumlah tugas berdasarkan hari deadline.</p>
                                </div>
                                <span class="rounded-full bg-soft-bg px-3 py-1 text-xs font-bold {{ $mutedClass }} ring-1 ring-soft-border">{{ $data['weekly_task_count'] ?? 0 }} tugas</span>
                            </div>
                            @php $maxDaily = max(1, collect($data['daily_workload'] ?? [])->max('count') ?? 1); @endphp
                            <div class="flex h-52 items-end gap-3 border-b border-soft-border pb-3">
                                @foreach($data['daily_workload'] ?? [] as $day)
                                    @php $height = max(8, ((int) ($day['count'] ?? 0) / $maxDaily) * 100); @endphp
                                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2 group">
                                        <div class="flex h-40 w-full items-end rounded-xl bg-soft-bg-light px-1 transition-colors group-hover:bg-soft-bg">
                                            <div class="w-full rounded-lg transition-all duration-500" style="height: {{ $height }}%; background-color: {{ $day['color'] ?? '#ccc' }}"></div>
                                        </div>
                                        <span class="text-[11px] font-bold {{ $mutedClass }}">{{ $day['day'] ?? '-' }}</span>
                                        <span class="text-[10px] {{ $mutedClass }}">{{ $day['count'] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <h2 class="text-sm font-bold">Tugas Mendatang</h2>
                            <div class="mt-4 space-y-3">
                                @forelse($data['tugas_mendatang'] ?? [] as $tugas)
                                    <div class="rounded-xl border border-soft-border p-3 hover:bg-soft-bg-light/50 transition-colors">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold">{{ $tugas['judul'] ?? 'Tugas' }}</p>
                                                <p class="mt-1 text-xs {{ $mutedClass }}">{{ $tugas['matkul'] ?? '-' }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-soft-bg px-2 py-1 text-[10px] font-bold {{ $mutedClass }} ring-1 ring-soft-border">{{ $tugas['sisa'] ?? '-' }}</span>
                                        </div>
                                        <p class="mt-2 text-[11px] {{ $mutedClass }}">{{ $tugas['deadline'] ?? '-' }} · {{ $tugas['jam'] ?? '-' }}</p>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Belum ada tugas mendatang.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                @endif

                {{-- CALENDAR TAB --}}
                @if($currentTab === 'calendar')
                    <section class="grid gap-6 xl:grid-cols-[1.45fr_.8fr]">
                        <div class="{{ $cardClass }} p-5 sm:p-6 hover:shadow-lg">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                {{-- BULLETPROOF: Tambahkan ?: now() agar tidak error jika tanggal null/kosong --}}
                                <h2 class="text-sm font-bold">{{ Carbon::parse($data['month_start'] ?: now())->translatedFormat('F Y') }}</h2>
                                <div class="flex gap-2">
                                    <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_previous']['month'] ?? now()->month, 'year' => $data['calendar_previous']['year'] ?? now()->year]) }}" class="rounded-full border border-soft-border px-3 py-1 text-sm font-bold hover:bg-soft-bg transition">‹</a>
                                    <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_next']['month'] ?? now()->month, 'year' => $data['calendar_next']['year'] ?? now()->year]) }}" class="rounded-full border border-soft-border px-3 py-1 text-sm font-bold hover:bg-soft-bg transition">›</a>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 border-b border-soft-border pb-3 text-center text-[11px] font-bold uppercase {{ $mutedClass }}">
                                <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-2 text-xs">
                                @php
                                    $monthStart = Carbon::parse($data['month_start'] ?: now());
                                    $daysInMonth = Carbon::parse($data['month_end'] ?: now())->day;
                                    $emptyCells = $monthStart->dayOfWeekIso === 7 ? 0 : $monthStart->dayOfWeekIso;
                                @endphp
                                @for($i = 0; $i < $emptyCells; $i++)
                                    <div class="min-h-16 rounded-xl bg-soft-bg-light/40 hover:bg-soft-bg-light transition-colors"></div>
                                @endfor
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $taskCount = count($data['monthly_tasks'][$day] ?? []);
                                        $dayStatus = BebanCalculator::forCount($taskCount);
                                        
                                        $colorClass = match($dayStatus) {
                                            BebanCalculator::LIGHT    => 'bg-green-50 border-green-200 hover:bg-green-100',
                                            BebanCalculator::NORMAL   => 'bg-amber-50 border-amber-200 hover:bg-amber-100',
                                            BebanCalculator::HEAVY    => 'bg-red-50 border-red-200 hover:bg-red-100',
                                            BebanCalculator::OVERLOAD => 'bg-red-100 border-red-300 hover:bg-red-200',
                                            default                   => 'bg-soft-bg-light border-soft-border hover:bg-soft-bg',
                                        };
                                    @endphp
                                    <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $monthStart->month, 'year' => $monthStart->year, 'day' => $day]) }}"
                                       @class([
                                           $colorClass,
                                           'ring-2 ring-pastel-biru ring-offset-2' => ($data['selected_day'] ?? null) === $day,
                                           'flex min-h-16 flex-col justify-between rounded-xl border p-2 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm',
                                       ])>
                                        <span class="font-bold">{{ $day }}</span>
                                        @if($taskCount > 0)
                                            <span class="text-[10px] font-bold {{ $mutedClass }}">{{ $taskCount }} Tugas</span>
                                        @endif
                                    </a>
                                @endfor
                            </div>
                        </div>

                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <h2 class="text-sm font-bold">Timeline Deadline</h2>
                            <p class="mt-1 text-xs {{ $mutedClass }}">{{ Carbon::parse($data['selected_date'] ?: now())->translatedFormat('l, d F Y') }}</p>
                            <div class="mt-5 space-y-4">
                                @forelse($data['selected_day_tasks'] ?? [] as $task)
                                    <div class="flex gap-3">
                                        <span class="w-12 shrink-0 font-mono text-[11px] {{ $mutedClass }}">{{ $task['jam'] ?? '-' }}</span>
                                        <div class="border-l-2 border-pastel-biru/40 pl-3">
                                            <p class="text-sm font-bold">{{ $task['judul'] ?? 'Deadline' }}</p>
                                            <p class="mt-1 text-xs {{ $mutedClass }}">{{ $task['matkul'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Tidak ada deadline pada tanggal ini.</p>
                                @endforelse
                            </div>

                            @if(!empty($data['tugas_terlambat']))
                                <div class="mt-6 border-t border-soft-border pt-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-appleRed">Tugas Terlambat</h3>
                                        <span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-bold text-appleRed ring-1 ring-red-100">
                                            {{ count($data['tugas_terlambat']) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-3">
                                        @foreach($data['tugas_terlambat'] as $tugas)
                                            <div class="flex gap-3">
                                                <span class="w-12 shrink-0 font-mono text-[11px] {{ $mutedClass }}">{{ $tugas['jam'] ?? '-' }}</span>
                                                <div class="border-l-2 border-appleRed/40 pl-3">
                                                    <p class="text-sm font-bold">{{ $tugas['judul'] }}</p>
                                                    <p class="mt-1 text-xs {{ $mutedClass }}">{{ $tugas['matkul'] }} · {{ $tugas['deadline'] }}</p>
                                                    <p class="mt-0.5 text-[11px] font-semibold text-appleRed">{{ $tugas['terlambat'] }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- MONITORING TAB --}}
                @if($currentTab === 'monitoring')
                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['SKS Aktif', ($profile['sks_semester'] ?? 0) . ' SKS', 'Semester ' . ($profile['semester'] ?? '-'), 'text-soft-dark'],
                            ['SKS Lulus', ($profile['sks_lulus'] ?? 0) . ' SKS', 'Total akumulatif', 'text-pastel-hijau-atas'],
                            ['IPK Kumulatif', $ipkKumulatif, $ipkCaption, 'text-soft-dark'],
                            ['Sisa SKS', (isset($profile['prediksi_lulus']['sisa_sks']) ? $profile['prediksi_lulus']['sisa_sks'] : max(0, 144 - ($profile['sks_lulus'] ?? 0))) . ' SKS', 'Sisa ' . (isset($profile['prediksi_lulus']['sisa_semester']) ? $profile['prediksi_lulus']['sisa_semester'] : max(0, 8 - ($profile['semester'] ?? 1))) . ' semester. Semangat!', 'text-pastel-ungu'],
                        ] as [$label, $value, $caption, $color])
                            <div class="{{ $cardClass }} p-5 hover:shadow-lg hover:-translate-y-1">
                                <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">{{ $label }}</p>
                                <p class="mt-3 text-3xl font-bold tracking-tight {{ $color }}">{{ $value }}</p>
                                <p class="mt-1 text-[11px] {{ $mutedClass }}">{{ $caption }}</p>
                            </div>
                        @endforeach
                    </section>

                    <section class="{{ $cardClass }} overflow-hidden hover:shadow-lg">
                        <div class="border-b border-soft-border p-5">
                            <h2 class="text-sm font-bold">Mata Kuliah Semester Ini</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-soft-bg text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">
                                    <tr>
                                        <th class="px-5 py-3">Mata Kuliah</th>
                                        <th class="px-5 py-3 text-center">SKS</th>
                                        <th class="px-5 py-3 text-center">Total Tugas</th>
                                        <th class="px-5 py-3 text-center">Minggu Ini</th>
                                        <th class="px-5 py-3 text-center">Beban</th>
                                        <th class="px-5 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-soft-border/70">
                                    @forelse($data['matakuliah'] ?? [] as $mk)
                                        <tr class="hover:bg-soft-bg-light/70 transition-colors">
                                            {{-- BULLETPROOF: Tambahkan ?? untuk semua key database --}}
                                            <td class="px-5 py-4">
                                                <p class="font-bold">{{ $mk['nama'] ?? 'Mata Kuliah' }}</p>
                                                <p class="mt-1 text-xs {{ $mutedClass }}">{{ $mk['kode'] ?? '-' }}</p>
                                                @if(!empty($mk['tugas_nilai']))
                                                    <div class="mt-2 space-y-1">
                                                        @foreach($mk['tugas_nilai'] as $tugas)
                                                            <div class="flex justify-between gap-3 text-xs text-gray-500">
                                                                <span>{{ $tugas['nama'] }} ({{ $tugas['bobot'] }}%)</span>
                                                                <span class="{{ $tugas['nilai'] !== null ? 'font-semibold text-gray-800' : 'text-gray-400' }}">
                                                                    {{ $tugas['nilai'] !== null ? number_format($tugas['nilai'], 1) : '-' }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $mutedClass }}">{{ $mk['sks'] ?? 0 }}</td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $mutedClass }}">{{ $mk['tugas'] ?? 0 }}</td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $mutedClass }}">{{ $mk['tugas_minggu_ini'] ?? 0 }}</td>
                                            <td class="px-5 py-4 text-center"><span class="rounded-full border px-3 py-1 text-[11px] font-bold {{ $mk['beban_color'] ?? 'text-gray-500' }}">{{ $mk['beban'] ?? 'Normal' }}</span></td>
                                            <td class="px-5 py-4 text-center text-xs font-bold text-blue-600">{{ $mk['status'] ?? 'Aktif' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-5 py-8 text-center text-sm {{ $mutedClass }}">Belum ada mata kuliah aktif.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                {{-- ANALYTICS TAB --}}
                @if($currentTab === 'analytics')
                    <section class="grid gap-6 xl:grid-cols-[1fr_.9fr]">
                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-sm font-bold">Prediksi Risiko Akademik</h2>
                                    <p class="mt-1 text-xs {{ $mutedClass }}">Berdasarkan beban tugas dan performa akademik.</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-4xl font-bold tracking-tight text-pastel-ungu">{{ $data['risk_score'] ?? 0 }}%</p>
                                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Risiko</p>
                                </div>
                            </div>
                            <div class="mt-6" style="width: 100%;">
                                <div style="width: 100%; height: 12px; background-color: #F5F1E8; border-radius: 9999px; overflow: hidden;">
                                    <div style="width: {{ min(100, max(0, (int) ($data['risk_score'] ?? 0))) }}%; height: 100%; background: linear-gradient(to right, #F97316, #EF4444); border-radius: 9999px; transition: width 1s ease-out;"></div>
                                </div>
                            </div>
                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-green-100 bg-green-50 p-3"><p class="text-xs font-bold text-pastel-hijau-atas">Aman</p><p class="text-[11px] {{ $mutedClass }}">0-40%</p><p class="mt-1 text-[11px] {{ $mutedClass }}">Anda tidak perlu cemas, beban masih dalam batas wajar untuk jadwal Anda saat ini.</p></div>
                                <div class="rounded-xl border border-orange-100 bg-orange-50 p-3"><p class="text-xs font-bold text-pastel-ungu">Perlu Perhatian</p><p class="text-[11px] {{ $mutedClass }}">40-70%</p><p class="mt-1 text-[11px] {{ $mutedClass }}">Mulai atur waktu dari sekarang. Cicil tugas sedikit demi sedikit agar tidak menumpuk di akhir minggu.</p></div>
                                <div class="rounded-xl border border-red-100 bg-red-50 p-3"><p class="text-xs font-bold text-appleRed">Risiko Tinggi</p><p class="text-[11px] {{ $mutedClass }}">70-100%</p><p class="mt-1 text-[11px] {{ $mutedClass }}">Prioritaskan kesehatanmu, kurangi kegiatan di luar jika memungkinkan, dan selesaikan tugas yang paling mendesak terlebih dahulu.</p></div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gradient-to-br from-pastel-hijau-atas to-pastel-hijau-bawah p-6 text-soft-dark shadow-lg">
                            <h2 class="text-sm font-bold">Rekomendasi Akademik</h2>
                            <div class="mt-4 space-y-3 text-sm">
                                <div class="rounded-xl bg-white/30 p-4 backdrop-blur-sm ring-1 ring-white/40">
                                    <p class="font-bold text-pastel-ungu">SKS Semester Depan</p>
                                    <p class="mt-1 text-xs text-soft-dark/75">Disarankan mengambil maksimal {{ $data['sks_recommendation']['sks'] ?? 24 }} SKS. {{ $data['sks_recommendation']['reason'] ?? '' }}</p>
                                </div>
                                <div class="rounded-xl bg-white/30 p-4 backdrop-blur-sm ring-1 ring-white/40">
                                    <p class="font-bold">Prioritas Fokus</p>
                                    <p class="mt-1 text-xs text-soft-dark/75">
                                        @if(($data['risk_score'] ?? 0) >= 70)
                                            Kurangi penumpukan deadline dan konsultasikan beban dengan dosen PA.
                                        @elseif(($data['risk_score'] ?? 0) >= 40)
                                            Pantau mata kuliah dengan beban mingguan tertinggi.
                                        @else
                                            Beban akademik masih stabil, pertahankan ritme belajar.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <h2 class="text-sm font-bold">Tren Historis IPK</h2>
                            @if(count($data['ipk_history'] ?? []))
                                @php
                                    $history = collect($data['ipk_history'])->values();
                                    $chartConfig = [
                                        'width' => 640, 'height' => 220,
                                        'paddingX' => 42, 'paddingTop' => 18, 'paddingBottom' => 34,
                                        'maxIpk' => 4.0,
                                    ];
                                    
                                    $plotWidth = $chartConfig['width'] - ($chartConfig['paddingX'] * 2);
                                    $plotHeight = $chartConfig['height'] - $chartConfig['paddingTop'] - $chartConfig['paddingBottom'];
                                    $lastIndex = max(1, $history->count() - 1);

                                    $points = $history->map(function ($ipkEntry, $index) use ($chartConfig, $plotWidth, $plotHeight, $lastIndex) {
                                        $ipkValue = min($chartConfig['maxIpk'], max(0, (float) ($ipkEntry['ipk'] ?? 0)));
                                        return [
                                            'x' => $chartConfig['paddingX'] + (($index / $lastIndex) * $plotWidth),
                                            'y' => $chartConfig['paddingTop'] + ($plotHeight - (($ipkValue / $chartConfig['maxIpk']) * $plotHeight)),
                                            'ipk' => $ipkEntry['ipk'] ?? 0,
                                            'semester' => $ipkEntry['semester'] ?? 'Sem',
                                        ];
                                    });
                                    
                                    $pointsArray = $points->toArray();
                                    $count = count($pointsArray);
                                    $smoothPath = '';
                                    
                                    if ($count > 0) {
                                        $smoothPath = 'M ' . $pointsArray[0]['x'] . ' ' . $pointsArray[0]['y'];
                                        for ($i = 1; $i < $count; $i++) {
                                            $prev = $pointsArray[$i - 1];
                                            $curr = $pointsArray[$i];
                                            $cp1x = $prev['x'] + ($curr['x'] - $prev['x']) / 3;
                                            $cp2x = $curr['x'] - ($curr['x'] - $prev['x']) / 3;
                                            $smoothPath .= " C $cp1x $prev[y], $cp2x $curr[y], $curr[x] $curr[y]";
                                        }
                                    }
                                    
                                    $areaPath = $smoothPath . ' L ' . $pointsArray[$count-1]['x'] . ' ' . ($chartConfig['height'] - $chartConfig['paddingBottom']) . ' L ' . $pointsArray[0]['x'] . ' ' . ($chartConfig['height'] - $chartConfig['paddingBottom']) . ' Z';
                                @endphp
                                <div class="mt-6 overflow-x-auto">
                                    <svg data-ipk-chart class="min-w-[560px]" viewBox="0 0 {{ $chartConfig['width'] }} {{ $chartConfig['height'] }}" role="img" aria-label="Grafik Tren Historis IPK">
                                        <defs>
                                            <linearGradient id="ipkTrendFill" x1="0" x2="0" y1="0" y2="1">
                                                <stop offset="0%" stop-color="#111827" stop-opacity="0.15" />
                                                <stop offset="100%" stop-color="#111827" stop-opacity="0.0" />
                                            </linearGradient>
                                            <filter id="glow">
                                                <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
                                                <feMerge>
                                                    <feMergeNode in="coloredBlur"/>
                                                    <feMergeNode in="SourceGraphic"/>
                                                </feMerge>
                                            </filter>
                                        </defs>

                                        @foreach([4, 3, 2, 1, 0] as $tick)
                                            @php $tickY = $chartConfig['paddingTop'] + ($plotHeight - (($tick / $chartConfig['maxIpk']) * $plotHeight)); @endphp
                                            <line x1="{{ $chartConfig['paddingX'] }}" y1="{{ $tickY }}" x2="{{ $chartConfig['width'] - $chartConfig['paddingX'] }}" y2="{{ $tickY }}" stroke="#E7E0D6" stroke-width="1" stroke-dasharray="4 4" />
                                            <text x="{{ $chartConfig['paddingX'] - 12 }}" y="{{ $tickY + 4 }}" text-anchor="end" class="fill-current {{ $mutedClass }}" font-size="10" font-weight="600">{{ number_format($tick, 1) }}</text>
                                        @endforeach

                                        <path d="{{ $areaPath }}" fill="url(#ipkTrendFill)" />
                                        <path d="{{ $smoothPath }}" fill="none" stroke="#111827" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" filter="url(#glow)" />

                                        @foreach($pointsArray as $point)
                                            <g class="group cursor-pointer">
                                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="20" fill="transparent" />
                                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="#111827" stroke="#FFFFFF" stroke-width="3" class="group-hover:hidden transition-all" />
                                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="7" fill="#111827" stroke="#FFFFFF" stroke-width="4" class="hidden group-hover:block transition-all" />
                                                
                                                <g class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                                    <rect x="{{ $point['x'] - 32 }}" y="{{ $point['y'] - 40 }}" width="64" height="26" rx="6" fill="#111827" class="drop-shadow-lg" />
                                                    <text x="{{ $point['x'] }}" y="{{ $point['y'] - 22 }}" text-anchor="middle" fill="#FFFFFF" font-size="11" font-weight="bold">{{ number_format($point['ipk'], 2) }}</text>
                                                    <polygon points="{{ $point['x'] - 4 }},{{ $point['y'] - 14 }} {{ $point['x'] + 4 }},{{ $point['y'] - 14 }} {{ $point['x'] }},{{ $point['y'] - 10 }}" fill="#111827" />
                                                </g>
                                                
                                                <text x="{{ $point['x'] }}" y="{{ $chartConfig['height'] - 10 }}" text-anchor="middle" class="fill-current {{ $mutedClass }}" font-size="10" font-weight="600">{{ $point['semester'] }}</text>
                                            </g>
                                        @endforeach
                                    </svg>
                                </div>
                            @else
                                <p class="mt-4 rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Belum ada riwayat IPK.</p>
                            @endif
                        </div>

                        <div class="{{ $cardClass }} p-6 hover:shadow-lg">
                            <h2 class="text-sm font-bold">Kompetensi Mata Kuliah</h2>
                            <div class="mt-5 space-y-4">
                                @forelse($data['competency'] ?? [] as $skill)
                                    <div>
                                        <div class="flex justify-between gap-3 text-xs">
                                            <span class="font-bold">{{ $skill['nama'] ?? 'Kompetensi' }}</span>
                                            <span class="{{ $mutedClass }}">{{ $skill['label'] ?? '-' }} · {{ $skill['score'] ?? 0 }}</span>
                                        </div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-soft-bg"><div class="h-full rounded-full bg-blue-500 transition-all duration-500" style="width: {{ min(100, max(0, (int) ($skill['score'] ?? 0))) }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Belum ada nilai tugas yang bisa dihitung menjadi kompetensi.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                {{-- NOTIFICATIONS TAB --}}
                @if($currentTab === 'notifications')
                    <section class="{{ $cardClass }} divide-y divide-soft-border/70 overflow-hidden hover:shadow-lg">
                        @forelse($data['notifikasi'] ?? [] as $notif)
                            @php
                                $borderColor = match($notif['tipe'] ?? '') {
                                    'peringatan' => 'border-appleRed',
                                    'pengingat'  => 'border-pastel-ungu',
                                    'sukses'     => 'border-pastel-hijau-atas',
                                    default      => 'border-pastel-biru',
                                };
                            @endphp
                            <div class="flex gap-4 p-5 hover:bg-soft-bg-light/70 border-l-4 {{ $borderColor }} transition-colors">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-sm font-bold">{{ $notif['judul'] ?? 'Notifikasi' }}</h2>
                                        @if($notif['unread'] ?? false)<span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>@endif
                                    </div>
                                    <p class="mt-1 text-sm leading-relaxed {{ $mutedClass }}">{{ $notif['desc'] ?? '' }}</p>
                                    <p class="mt-2 font-mono text-[11px] {{ $mutedClass }}">{{ $notif['waktu'] ?? '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="p-6 text-sm {{ $mutedClass }} text-center">Belum ada notifikasi.</p>
                        @endforelse
                    </section>
                @endif

                {{-- TUGAS TAB --}}
                @if($currentTab === 'tugas')
                    @php $selectedMkId = request('mk'); @endphp

                    @if(session('status'))
                        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(!$selectedMkId)
                        <section class="{{ $cardClass }} p-6">
                            <h2 class="text-sm font-bold text-gray-700">Pilih Mata Kuliah</h2>
                            <div class="mt-4 space-y-3">
                                @forelse($data['tugas_tab'] ?? [] as $mk)
                                    <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => 'tugas', 'mk' => $mk['mk_id']]) }}"
                                       class="flex items-center justify-between gap-4 rounded-xl border border-soft-border bg-white px-5 py-4 transition hover:border-indigo-400 hover:shadow-sm">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ $mk['kode'] }}</p>
                                            <p class="mt-0.5 truncate text-sm font-semibold text-soft-dark">{{ $mk['nama'] }}</p>
                                        </div>
                                        @php $pending = collect($mk['tugas'])->where('submitted', false)->count(); @endphp
                                        @if($pending > 0)
                                            <span class="shrink-0 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                                {{ $pending }} belum dikumpul
                                            </span>
                                        @else
                                            <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">Semua terkumpul</span>
                                        @endif
                                    </a>
                                @empty
                                    <p class="rounded-xl bg-soft-bg p-4 text-sm {{ $mutedClass }} text-center">Tidak ada mata kuliah aktif.</p>
                                @endforelse
                            </div>
                        </section>
                    @else
                        @php $currentMk = collect($data['tugas_tab'] ?? [])->firstWhere('mk_id', (int) $selectedMkId); @endphp

                        @if(!$currentMk)
                            <p class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">Mata kuliah tidak ditemukan.</p>
                        @else
                            <div class="flex items-center gap-3">
                                <a wire:navigate href="{{ route('siswa.dashboard', ['tab' => 'tugas']) }}"
                                   class="text-xs text-indigo-600 hover:underline">← Semua Kelas</a>
                                <span class="text-gray-300">|</span>
                                <span class="text-sm font-semibold">{{ $currentMk['kode'] }} — {{ $currentMk['nama'] }}</span>
                            </div>

                            <section class="space-y-4">
                                @forelse($currentMk['tugas'] as $tugas)
                                    @php
                                        $deadline = Carbon::parse($tugas['deadline']);
                                        $isLate = now()->gt($deadline) && !$tugas['submitted'];
                                    @endphp
                                    <div class="rounded-xl border {{ $tugas['submitted'] ? 'border-green-200 bg-green-50' : ($isLate ? 'border-red-200 bg-red-50' : 'border-soft-border bg-white') }} p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-soft-dark">{{ $tugas['nama'] }}</p>
                                                <p class="mt-0.5 text-xs {{ $mutedClass }}">
                                                    Deadline: {{ $deadline->translatedFormat('d M Y, H:i') }}
                                                    · Bobot: {{ $tugas['bobot'] }}%
                                                </p>
                                                @if($tugas['submitted'])
                                                    <p class="mt-1 text-xs font-medium {{ $tugas['status'] === 'late' ? 'text-orange-600' : 'text-green-700' }}">
                                                        ✓ Dikumpulkan {{ Carbon::parse($tugas['submitted_at'])->translatedFormat('d M Y, H:i') }}
                                                        {{ $tugas['status'] === 'late' ? '(Terlambat)' : '' }}
                                                    </p>
                                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                                        <span class="{{ $mutedClass }}">{{ $tugas['file_name'] }}</span>
                                                        <a wire:navigate href="{{ route('siswa.submission.download', $tugas['id']) }}"
                                                           class="font-medium text-indigo-600 hover:underline">Download</a>
                                                    </div>
                                                @elseif($isLate)
                                                    <p class="mt-1 text-xs font-bold text-red-600">Deadline terlewat — submit akan tercatat terlambat</p>
                                                @endif
                                            </div>
                                            @if($tugas['submitted'])
                                                <span class="shrink-0 rounded-full bg-green-200 px-3 py-1 text-xs font-bold text-green-800">Selesai</span>
                                            @elseif($isLate)
                                                <span class="shrink-0 rounded-full bg-red-200 px-3 py-1 text-xs font-bold text-red-800">Terlambat</span>
                                            @else
                                                <span class="shrink-0 rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">Belum</span>
                                            @endif
                                        </div>

                                        <form method="POST"
                                              action="{{ route('siswa.tugas.submit', $tugas['id']) }}"
                                              enctype="multipart/form-data"
                                              class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
     ... (7 KB left)