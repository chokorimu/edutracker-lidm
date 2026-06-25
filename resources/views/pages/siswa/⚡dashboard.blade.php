@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-bone-light text-appleDark font-sans overflow-hidden selection:bg-appleDark selection:text-white">

    <aside class="w-64 bg-white/60 backdrop-blur-xl border-r border-bone-dark/60 flex flex-col justify-between z-20 flex-shrink-0">
        <div class="p-6 space-y-8">
            <div class="px-2">
                <span class="text-xl font-bold tracking-tighter lowercase select-none block">EduTrack</span>
                <span class="text-[10px] font-bold text-appleMuted uppercase tracking-widest mt-1">Sistem Beban Akademik</span>
            </div>

            <nav class="space-y-1">
                <a href="{{ route('siswa.dashboard', ['tab' => 'dashboard']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'dashboard' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('siswa.dashboard', ['tab' => 'calendar']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'calendar' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Kalender Tugas
                </a>
                <a href="{{ route('siswa.dashboard', ['tab' => 'monitoring']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'monitoring' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Monitoring SKS
                </a>
                <a href="{{ route('siswa.dashboard', ['tab' => 'analytics']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'analytics' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Analitik Akademik
                </a>
                <a href="{{ route('siswa.dashboard', ['tab' => 'notifications']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'notifications' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Notifikasi
                </a>
                <a href="{{ route('siswa.dashboard', ['tab' => 'profile']) }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-sm font-medium transition-all {{ $currentTab === 'profile' ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profil
                </a>
            </nav>
        </div>
        
        <div class="p-6 border-t border-bone-dark/50">
            <form method="POST" action="{{ route('siswa.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-appleRed hover:bg-red-50 text-sm font-medium transition-all text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar Sesi
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen relative">
        
        <header class="h-20 bg-bone-light/80 backdrop-blur-xl border-b border-bone-dark/50 px-8 flex items-center justify-between z-10 sticky top-0">
            <div class="relative w-full max-w-md">
                <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-appleMuted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari tugas, mata kuliah..." class="w-full bg-white border border-bone-dark rounded-full pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:border-appleDark focus:ring-1 focus:ring-appleDark transition-all placeholder:text-appleMuted">
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 pl-4">
                    <div class="w-9 h-9 rounded-full bg-appleDark text-white flex items-center justify-center text-xs font-bold shadow-sm">{{ strtoupper(substr($data['profile']['nama'], 0, 2)) }}</div>
                    <div>
                        <p class="text-xs font-bold text-appleDark leading-tight">{{ $data['profile']['nama'] }}</p>
                        <p class="text-[10px] text-appleMuted">NIM: {{ $data['profile']['nim'] }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto no-scrollbar p-8 space-y-6 pb-24">

            @if($currentTab === 'dashboard')
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-appleDark">Dashboard Akademik</h1>
                    <p class="text-sm text-appleMuted mt-1">Monitoring beban akademik dan deadline tugas Anda</p>
                </div>

                @php $status = $data['profile']['weekly_status'] ?? 'ringan'; @endphp
                @if($status === "berat" || $status === "overload")
                    <div class="bg-[#FFF4E5] border border-[#FFE0B2] rounded-[24px] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="bg-appleOrange/20 text-appleOrange p-2 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                            <div>
                                <h3 class="text-sm font-bold text-appleDark">Peringatan: Beban Akademik @if($status === "overload") Overload @else Tinggi @endif</h3>
                                <p class="text-xs text-appleDark/70 mt-0.5">Beban akademik Anda minggu ini melebihi batas aman. Segera rencanakan prioritas tugas.</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-appleDark mb-4">Distribusi Beban Mingguan</h3>
                    <canvas id="workloadChart" height="80"></canvas>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctx = document.getElementById("workloadChart").getContext("2d");
                    const workloadData = @json($data["daily_workload"]);
                    new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: workloadData.map(d => d.day),
                            datasets: [{
                                label: "Jumlah Tugas",
                                data: workloadData.map(d => d.count),
                                backgroundColor: workloadData.map(d => d.color),
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                        }
                    });
                </script>
                                <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-appleDark mb-4">Kalender Tugas Bulanan</h3>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-bold text-appleDark">{{ \Carbon\Carbon::parse($data['month_start'])->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="grid grid-cols-7 text-center text-xs font-bold text-appleMuted border-b border-bone-dark pb-3 mb-3">
                        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 text-xs">
                        @php
                            $firstDayOfMonth = \Carbon\Carbon::parse($data['month_start']);
                            $daysInMonth = \Carbon\Carbon::parse($data['month_end'])->day;
                            $startOfWeek = $firstDayOfMonth->dayOfWeekIso; 
                            $emptyCells = ($startOfWeek === 7) ? 0 : $startOfWeek;

                            for ($i = 0; $i < $emptyCells; $i++) {
                                echo '<div class="p-2 min-h-[64px]"></div>';
                            }
                        @endphp

                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $taskCount = $data['monthly_tasks']->has($day) ? $data['monthly_tasks']->get($day)->count() : 0;
                                $dayStatus = App\Services\BebanCalculator::forCount($taskCount);

                                $colorClass = match($dayStatus) {
                                    App\Services\BebanCalculator::LIGHT => 'bg-green-50 border-green-200/60',
                                    App\Services\BebanCalculator::NORMAL => 'bg-amber-50 border-amber-200/60',
                                    App\Services\BebanCalculator::HEAVY => 'bg-red-50 border-red-200/60',
                                    App\Services\BebanCalculator::OVERLOAD => 'bg-red-100 border-red-300/60',
                                    default => 'bg-bone border-bone-dark',
                                };
                            @endphp
                            <div class="{{ $colorClass }} p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">{{ $day }}</span>
                                @if($taskCount > 0)
                                    <span class="text-[9px] text-appleMuted font-medium">{{ $taskCount }} Tugas</span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Total SKS Aktif</span>
                        <h2 class="text-3xl font-bold text-appleDark mt-2">{{ $data['profile']['sks_semester'] }}</h2>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Tugas Minggu Ini</span>
                        <h2 class="text-3xl font-bold text-appleDark mt-2">{{ $data['weekly_task_count'] }}</h2>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Deadline Terdekat</span>
                        <h2 class="text-3xl font-bold text-appleOrange mt-2">{{ $data['deadline_terdekat'] }}</h2>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Status Beban</span>
                        <h2 class="text-3xl font-bold {{ $data['status_beban_color'] }} mt-2">{{ $data['status_beban_label'] }}</h2>
                    </div>
                </div>
            @endif

            @if($currentTab === 'calendar')
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-appleDark">Kalender Akademik</h1>
                    <p class="text-sm text-appleMuted mt-1">Jadwal tugas dan deadline dalam satu tampilan komprehensif</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-sm font-bold text-appleDark">{{ \Carbon\Carbon::parse($data['month_start'])->translatedFormat('F Y') }}</span>
                            <div class="flex gap-1">
                                <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_previous']['month'], 'year' => $data['calendar_previous']['year']]) }}" class="p-1.5 border border-bone-dark rounded-full hover:bg-bone-light transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                                <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_next']['month'], 'year' => $data['calendar_next']['year']]) }}" class="p-1.5 border border-bone-dark rounded-full hover:bg-bone-light transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 text-center text-xs font-bold text-appleMuted border-b border-bone-dark pb-3 mb-3">
                            <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                        </div>

                        <div class="grid grid-cols-7 gap-1.5 text-xs">
                            @php
                                $firstDayOfMonth = \Carbon\Carbon::parse($data['month_start']);
                                $daysInMonth = \Carbon\Carbon::parse($data['month_end'])->day;
                                $startOfWeek = $firstDayOfMonth->dayOfWeekIso;
                                $emptyCells = ($startOfWeek === 7) ? 0 : $startOfWeek;

                                for ($i = 0; $i < $emptyCells; $i++) {
                                    echo '<div class="p-2 min-h-[64px]"></div>';
                                }
                            @endphp

                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $taskCount = $data['monthly_tasks']->has($day) ? $data['monthly_tasks']->get($day)->count() : 0;
                                    $dayStatus = App\Services\BebanCalculator::forCount($taskCount);

                                    $colorClass = match($dayStatus) {
                                        App\Services\BebanCalculator::LIGHT => 'bg-green-50 border-green-200/60',
                                        App\Services\BebanCalculator::NORMAL => 'bg-amber-50 border-amber-200/60',
                                        App\Services\BebanCalculator::HEAVY => 'bg-red-50 border-red-200/60',
                                        App\Services\BebanCalculator::OVERLOAD => 'bg-red-100 border-red-300/60',
                                        default => 'bg-bone border-bone-dark',
                                    };
                                @endphp
                                <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => \Carbon\Carbon::parse($data['month_start'])->month, 'year' => \Carbon\Carbon::parse($data['month_start'])->year, 'day' => $day]) }}" class="{{ $colorClass }} {{ $data['selected_day'] === $day ? 'ring-2 ring-appleDark' : '' }} border p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between hover:scale-[1.02] transition-transform">
                                    <span class="font-bold text-appleDark">{{ $day }}</span>
                                    @if($taskCount > 0)
                                        <span class="text-[9px] text-appleMuted font-medium">{{ $taskCount }} Tugas</span>
                                    @endif
                                </a>
                            @endfor
                        </div>
                    </div>

                    <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-appleDark mb-1">Timeline Deadline</h3>
                            <p class="text-[10px] text-appleMuted mb-4">{{ \Carbon\Carbon::parse($data['selected_date'])->translatedFormat('l, d F Y') }}</p>
                            <div class="space-y-3 mb-6">
                                @forelse($data['selected_day_tasks'] as $task)
                                    <div class="flex gap-3">
                                        <span class="text-[10px] font-mono text-appleMuted w-10">{{ $task['jam'] }}</span>
                                        <div class="border-l border-bone-dark pl-3 pb-3">
                                            <h4 class="text-xs font-bold text-appleDark leading-snug">{{ $task['judul'] }}</h4>
                                            <p class="text-[10px] text-appleMuted mt-0.5">{{ $task['matkul'] }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-appleMuted">Tidak ada deadline pada tanggal ini.</p>
                                @endforelse
                            </div>

                            <h3 class="text-sm font-bold text-appleDark mb-4">Tugas Mendatang</h3>
                            <div class="space-y-4">
                                @foreach($data['tugas_mendatang'] as $tugas)
                                    <div class="border-b border-bone-dark pb-3 last:border-0 last:pb-0 flex justify-between items-start gap-2">
                                        <div>
                                            <h4 class="text-xs font-bold text-appleDark leading-snug">{{ $tugas['judul'] }}</h4>
                                            <p class="text-[10px] text-appleMuted mt-0.5">{{ $tugas['matkul'] }} • {{ $tugas['deadline'] }} • {{ $tugas['sisa'] }}</p>
                                        </div>
                                        <span class="text-[10px] font-mono whitespace-nowrap text-appleMuted bg-bone px-2 py-0.5 rounded-full">{{ $tugas['jam'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($currentTab === 'monitoring')
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-appleDark">Monitoring SKS</h1>
                    <p class="text-sm text-appleMuted mt-1">Pantau distribusi beban SKS semester ini untuk kestabilan akademik</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold text-appleMuted uppercase tracking-wider block">SKS Aktif</span>
                        <p class="text-3xl font-bold text-appleDark mt-2">{{ $data['profile']['sks_semester'] }} SKS</p>
                        <p class="text-[10px] text-appleMuted mt-1">Semester {{ $data['profile']['semester'] }}</p>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold text-appleMuted uppercase tracking-wider block">SKS Lulus</span>
                        <p class="text-3xl font-bold text-appleGreen mt-2">{{ $data['profile']['sks_lulus'] }} SKS</p>
                        <p class="text-[10px] text-appleMuted mt-1">Total akumulatif</p>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold text-appleMuted uppercase tracking-wider block">IPK Kumulatif</span>
                        <p class="text-3xl font-bold text-appleDark mt-2">{{ $data['profile']['ipk'] }}</p>
                        <!-- Top 15% placeholder – calculated in controller -->
                    </div>
                </div>

                <div class="bg-white border border-bone-dark rounded-[24px] overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-bone-dark">
                        <h3 class="text-sm font-bold text-appleDark">Mata Kuliah Semester Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-bone text-appleMuted font-bold border-b border-bone-dark">
                                    <th class="p-4 pl-6">Mata Kuliah</th>
                                    <th class="p-4 text-center">SKS</th>
                                    <th class="p-4 text-center">Total Tugas</th>
                                    <th class="p-4 text-center">Minggu Ini</th>
                                    <th class="p-4 text-center">Beban</th>
                                    <th class="p-4 text-center pr-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bone-dark/60">
                                @foreach($data['matakuliah'] as $mk)
                                    <tr class="hover:bg-bone-light/60 transition-colors">
                                        <td class="p-4 pl-6 font-bold text-appleDark">{{ $mk['nama'] }}</td>
                                        <td class="p-4 text-center text-appleMuted font-medium">{{ $mk['sks'] }}</td>
                                        <td class="p-4 text-center text-appleMuted font-medium">{{ $mk['tugas'] }}</td>
                                        <td class="p-4 text-center text-appleMuted font-medium">{{ $mk['tugas_minggu_ini'] }}</td>
                                        <td class="p-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold tracking-tight {{ $mk['beban_color'] }}">
                                                {{ $mk['beban'] }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center pr-6"><span class="text-blue-500 font-bold text-[11px]">{{ $mk['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($currentTab === 'analytics')
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-appleDark">Analitik Akademik</h1>
                    <p class="text-sm text-appleMuted mt-1">Insight mendalam tentang performa akademik dan prediksi risiko</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm flex flex-col justify-between gap-6">
                        <div>
                            <h3 class="text-sm font-bold text-appleDark mb-1">Prediksi Risiko Akademik</h3>
                            <p class="text-xs text-appleMuted">Berdasarkan pola beban tugas dan performa Anda</p>
                        </div>

                        <div class="flex justify-center items-center py-4">
                            <div class="relative w-32 h-32 rounded-full border-8 border-bone flex items-center justify-center">
                                <div class="absolute inset-0 rounded-full border-8 border-appleOrange border-t-transparent border-l-transparent transform" style="transform: rotate({{ $data['risk_score'] * 3.6 + 45 }}deg);"></div>
                                <div class="text-center z-10">
                                    <span class="text-2xl font-bold block text-appleDark">{{ $data['risk_score'] }}%</span>
                                    <span class="text-[9px] text-appleMuted font-bold uppercase tracking-wider block">Risiko</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 text-center gap-2 text-[11px]">
                            <div class="bg-green-50 border border-green-100 p-2.5 rounded-[12px]"><span class="block font-bold text-appleGreen">Aman</span><span class="text-[9px] text-appleMuted">0-40%</span></div>
                            <div class="bg-orange-50 border border-orange-100 p-2.5 rounded-[12px] border-appleOrange/30"><span class="block font-bold text-appleOrange">Perlu Perhatian</span><span class="text-[9px] text-appleMuted">40-70%</span></div>
                            <div class="bg-red-50 border border-red-100 p-2.5 rounded-[12px]"><span class="block font-bold text-appleRed">Risiko Tinggi</span><span class="text-[9px] text-appleMuted">70-100%</span></div>
                        </div>
                    </div>

                    <div class="bg-appleDark text-white rounded-[24px] p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2"><svg class="w-4 h-4 text-appleOrange" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><h3 class="text-sm font-bold">Rekomendasi AI</h3></div>
                        <div class="space-y-3 text-xs leading-relaxed opacity-95">
                            <div class="bg-white/10 p-3 rounded-[16px]"><p class="font-bold text-appleOrange">SKS Semester Depan</p><p class="text-[11px] opacity-80 mt-1">Disarankan mengambil maksimal {{ $data['sks_recommendation']['sks'] }} SKS. {{ $data['sks_recommendation']['reason'] }}</p></div>
                            <div class="bg-white/10 p-3 rounded-[16px]"><p class="font-bold text-white">Prioritas Fokus</p><p class="text-[11px] opacity-80 mt-1">{{ $data['risk_score'] >= 70 ? 'Kurangi penumpukan deadline dan konsultasikan beban dengan dosen PA.' : ($data['risk_score'] >= 40 ? 'Pantau mata kuliah dengan beban mingguan tertinggi.' : 'Beban akademik masih stabil, pertahankan ritme belajar.') }}</p></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <h4 class="text-xs font-bold text-appleDark">Tren Historis IPK</h4>
                        <div class="h-32 flex items-end gap-4 mt-4 border-b border-l border-bone-dark p-2">
                            @foreach($data['ipk_history'] as $ipkEntry)
                                @php
                                    // Calculate height based on IPK value, assuming a max IPK of 4.0 for scaling purposes.
                                    // The chart container is h-32, which typically corresponds to 128px height.
                                    // We'll scale the IPK to fit within this height.
                                    $maxChartHeightPx = 128;
                                    $maxIpk = 4.0;
                                    $scaledHeight = ($ipkEntry['ipk'] / $maxIpk) * $maxChartHeightPx;
                                    // Ensure minimum height for visibility and handle 0 IPK gracefully
                                    $scaledHeight = max($scaledHeight, 10); // Minimum height of 10px

                                    // Calculate percentage for the bar's height attribute if needed, but direct style is better for px
                                    // $heightPercentage = ($ipkEntry['ipk'] / $maxIpk) * 100;
                                @endphp
                                <div class="w-full bg-bone h-[{{ $scaledHeight }}px] rounded-t-[4px] relative" style="height: {{ $scaledHeight }}px;">
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] text-appleMuted">{{ number_format($ipkEntry['ipk'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[9px] text-appleMuted mt-2 px-6">
                            @foreach($data['ipk_history'] as $ipkEntry)
                                <span>{{ $ipkEntry['semester'] }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-appleDark">Radar Kompetensi</h4>
                        @if(count($data['competency']))
                            <div class="h-48 mt-3">
                                <canvas id="competencyChart"></canvas>
                            </div>
                            <div class="p-4 bg-bone-light border border-bone-dark rounded-[16px] text-[11px] space-y-2 mt-2">
                                @foreach($data['competency'] as $skill)
                                    <div class="flex justify-between gap-3"><span>{{ $skill['nama'] }}</span><span class="font-bold">{{ $skill['label'] }} ({{ $skill['score'] }})</span></div>
                                @endforeach
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                const competencyData = @json($data['competency']);
                                new Chart(document.getElementById("competencyChart"), {
                                    type: "radar",
                                    data: {
                                        labels: competencyData.map(item => item.nama),
                                        datasets: [{
                                            label: "Nilai rata-rata",
                                            data: competencyData.map(item => item.score),
                                            borderColor: "#007AFF",
                                            backgroundColor: "rgba(0, 122, 255, 0.12)",
                                            pointBackgroundColor: "#007AFF"
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: { r: { min: 0, max: 100, ticks: { stepSize: 20 } } },
                                        plugins: { legend: { display: false } }
                                    }
                                });
                            </script>
                        @else
                            <p class="text-xs text-appleMuted mt-3">Belum ada nilai tugas yang bisa dihitung menjadi kompetensi.</p>
                        @endif
                    </div>
                </div>
            @endif

            @if($currentTab === 'notifications')
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-appleDark">Notifikasi</h1>
                        <p class="text-sm text-appleMuted mt-1">Pemberitahuan krusial tentang ritme perkuliahan Anda</p>
                    </div>
                    <button class="text-xs font-medium text-blue-500 hover:underline">Tandai Semua Dibaca</button>
                </div>

                <div class="bg-white border border-bone-dark rounded-[24px] overflow-hidden shadow-sm divide-y divide-bone-dark/60">
                    @foreach($data['notifikasi'] as $notif)
                        <div class="p-5 flex items-start justify-between gap-4 hover:bg-bone-light/40 transition-colors">
                            <div class="flex items-start gap-4">
                                <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0
                                    {{ $notif['tipe'] === 'peringatan' ? 'bg-appleRed' : ($notif['tipe'] === 'pengingat' ? 'bg-appleOrange' : ($notif['tipe'] === 'sukses' ? 'bg-appleGreen' : 'bg-blue-500')) }}">
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-appleDark flex items-center gap-2">
                                        {{ $notif['judul'] }}
                                        @if($notif['unread'])
                                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                        @endif
                                    </h4>
                                    <p class="text-xs text-appleMuted mt-1 leading-relaxed">{{ $notif['desc'] }}</p>
                                    <span class="text-[10px] text-appleMuted font-mono block mt-2">{{ $notif['waktu'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($currentTab === 'profile')
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-appleDark">Profil Mahasiswa</h1>
                    <p class="text-sm text-appleMuted mt-1">Informasi utama identitas akademik SIAKAD Anda</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm text-center space-y-4">
                        <div class="w-20 h-20 bg-appleDark text-white rounded-full mx-auto flex items-center justify-center text-2xl font-bold shadow-sm">{{ strtoupper(substr($data['profile']['nama'], 0, 2)) }}</div>
                        <div>
                            <h3 class="text-base font-bold text-appleDark">{{ $data['profile']['nama'] }}</h3>
                            <p class="text-xs text-appleMuted mt-0.5">NIM: {{ $data['profile']['nim'] }}</p>
                        </div>
                        <div class="border-t border-bone-dark pt-4 text-xs text-left space-y-2 text-appleMuted">
                            <p><span class="font-semibold text-appleDark block">Fakultas / Prodi</span> {{ $data['profile']['prodi'] }}</p>
                            <p><span class="font-semibold text-appleDark block">Email Terdaftar</span> {{ $data['profile']['email'] }}</p>
                            <p><span class="font-semibold text-appleDark block">Angkatan Aktif</span> Semester {{ $data['profile']['semester'] }} • Tahun {{ $data['profile']['angkatan'] }}</p>
                        </div>
                        <button class="w-full bg-bone border border-bone-dark text-appleDark py-2 rounded-full text-xs font-bold hover:bg-bone-dark transition-colors active:scale-95">Edit Profil</button>
                    </div>

                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-appleMuted">Ringkasan Nilai & Dosen Wali</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                                <div class="bg-bone p-3 rounded-[16px]"><span class="text-[10px] text-appleMuted block">IPK</span><span class="text-base font-bold text-appleDark">{{ $data['profile']['ipk'] }}</span></div>
                                <div class="bg-bone p-3 rounded-[16px]"><span class="text-[10px] text-appleMuted block">SKS Lulus</span><span class="text-base font-bold text-appleDark">{{ $data['profile']['sks_lulus'] }}</span></div>
                                <div class="bg-bone p-3 rounded-[16px]"><span class="text-[10px] text-appleMuted block">SKS Kontrak</span><span class="text-base font-bold text-appleDark">{{ $data['profile']['sks_semester'] }}</span></div>
                                <div class="bg-bone p-3 rounded-[16px]"><span class="text-[10px] text-appleMuted block">Semester</span><span class="text-base font-bold text-appleDark">{{ $data['profile']['semester'] }}</span></div>
                            </div>
                            <div class="border-t border-bone-dark pt-4 flex gap-4 items-center">
                                <div class="w-8 h-8 rounded-full bg-bone flex items-center justify-center text-xs font-bold text-appleMuted">PA</div>
                                <div>
                                    <span class="text-[10px] text-appleMuted block">Dosen Pembimbing Akademik</span>
                                    <span class="text-xs font-bold text-appleDark">{{ $data['profile']['dosen_pa'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-appleMuted">Pengaturan Akun</h4>
                            <div class="divide-y divide-bone-dark text-xs">
                                <div class="py-3 flex justify-between items-center hover:opacity-75 cursor-pointer"><span>Keamanan Kata Sandi</span><svg class="w-3 h-3 text-appleMuted" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>
                                <div class="py-3 flex justify-between items-center hover:opacity-75 cursor-pointer"><span>Integrasi API SIAKAD</span><svg class="w-3 h-3 text-appleMuted" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </main>
</div>
@endsection
