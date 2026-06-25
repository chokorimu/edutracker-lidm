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
                    <div class="w-9 h-9 rounded-full bg-appleDark text-white flex items-center justify-center text-xs font-bold shadow-sm">MH</div>
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
                                $status = App\Services\BebanCalculator::forCount($taskCount);

                                $colorClass = match($status) {
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
                        <h2 class="text-3xl font-bold text-appleDark mt-2">8</h2>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Deadline Terdekat</span>
                        <h2 class="text-3xl font-bold text-appleOrange mt-2">2</h2>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-appleMuted">Status Beban</span>
                        <h2 class="text-3xl font-bold text-appleRed mt-2">Berat</h2>
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
                            <span class="text-sm font-bold text-appleDark">Mei 2026</span>
                            <div class="flex gap-1">
                                <button class="p-1.5 border border-bone-dark rounded-full hover:bg-bone-light transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></button>
                                <button class="p-1.5 border border-bone-dark rounded-full hover:bg-bone-light transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></button>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 text-center text-xs font-bold text-appleMuted border-b border-bone-dark pb-3 mb-3">
                            <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                        </div>

                        <div class="grid grid-cols-7 gap-1.5 text-xs">
                            <div class="p-2 min-h-[64px]"></div><div class="p-2 min-h-[64px]"></div><div class="p-2 min-h-[64px]"></div><div class="p-2 min-h-[64px]"></div>
                            
                            <div class="bg-amber-50 border border-amber-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">1</span><span class="text-[9px] text-appleMuted font-medium">2 Tugas</span>
                            </div>
                            <div class="bg-green-50 border border-green-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">2</span><span class="text-[9px] text-appleMuted font-medium">1 Tugas</span>
                            </div>
                            <div class="bg-green-50 border border-green-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">3</span><span class="text-[9px] text-appleMuted font-medium">1 Tugas</span>
                            </div>
                            <div class="bg-amber-50 border border-amber-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">4</span><span class="text-[9px] text-appleMuted font-medium">3 Tugas</span>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">5</span><span class="text-[9px] text-appleMuted font-medium">5 SKS</span>
                            </div>
                            <div class="bg-amber-50 border border-amber-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">6</span><span class="text-[9px] text-appleMuted font-medium">2 Tugas</span>
                            </div>
                            <div class="bg-green-50 border border-green-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">7</span><span class="text-[9px] text-appleMuted font-medium">1 Tugas</span>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">8</span><span class="text-[9px] text-appleMuted font-medium">4 Tugas</span>
                            </div>
                            <div class="bg-amber-50 border border-amber-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">9</span><span class="text-[9px] text-appleMuted font-medium">2 Tugas</span>
                            </div>
                            <div class="bg-green-50 border border-green-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">10</span><span class="text-[9px] text-appleMuted font-medium">Aman</span>
                            </div>
                            <div class="bg-red-50 border border-red-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">11</span><span class="text-[9px] text-appleRed font-bold">4 Tugas</span>
                            </div>
                            <div class="bg-green-50 border border-green-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">12</span><span class="text-[9px] text-appleMuted font-medium">1 Tugas</span>
                            </div>
                            <div class="bg-amber-50 border border-amber-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">13</span><span class="text-[9px] text-appleMuted font-medium">2 Tugas</span>
                            </div>
                            <div class="bg-appleDark border border-appleDark text-white p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between shadow-sm">
                                <span class="font-bold">14</span><span class="text-[9px] opacity-80 font-bold">3 Tugas</span>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200/60 p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">15</span><span class="text-[9px] text-appleMuted font-medium">5 Tugas</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-appleDark mb-4">Tugas Mendatang</h3>
                            <div class="space-y-4">
                                @foreach($data['tugas_mendatang'] as $tugas)
                                    <div class="border-b border-bone-dark pb-3 last:border-0 last:pb-0 flex justify-between items-start gap-2">
                                        <div>
                                            <h4 class="text-xs font-bold text-appleDark leading-snug">{{ $tugas['judul'] }}</h4>
                                            <p class="text-[10px] text-appleMuted mt-0.5">{{ $tugas['matkul'] }} • {{ $tugas['deadline'] }}</p>
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
                        <p class="text-[10px] text-appleGreen font-semibold mt-1">Top 15% program studi</p>
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
                                    <th class="p-4 text-center">Tugas</th>
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
                                        <td class="p-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-tight
                                                {{ $mk['beban'] === 'Tinggi' ? 'bg-red-50 text-appleRed' : ($mk['beban'] === 'Sedang' ? 'bg-orange-50 text-appleOrange' : 'bg-green-50 text-appleGreen') }}">
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
                                <div class="absolute inset-0 rounded-full border-8 border-appleOrange border-t-transparent border-l-transparent transform rotate-45"></div>
                                <div class="text-center z-10">
                                    <span class="text-2xl font-bold block text-appleDark">35%</span>
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
                            <div class="bg-white/10 p-3 rounded-[16px]"><p class="font-bold text-appleOrange">SKS Semester Depan</p><p class="text-[11px] opacity-80 mt-1">Disarankan mengambil maksimal 18 SKS berdasarkan kestabilan beban.</p></div>
                            <div class="bg-white/10 p-3 rounded-[16px]"><p class="font-bold text-white">Prioritas Fokus</p><p class="text-[11px] opacity-80 mt-1">Fokuskan perhatian ekstra pada mata kuliah kategori beban tinggi.</p></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm">
                        <h4 class="text-xs font-bold text-appleDark">Tren Historis IPK</h4>
                        <div class="h-32 flex items-end gap-4 mt-4 border-b border-l border-bone-dark p-2">
                            <div class="w-full bg-bone h-[70%] rounded-t-[4px] relative"><span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] text-appleMuted">3.1</span></div>
                            <div class="w-full bg-bone h-[78%] rounded-t-[4px] relative"><span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] text-appleMuted">3.3</span></div>
                            <div class="w-full bg-appleDark h-[86%] rounded-t-[4px] relative"><span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] font-bold text-appleDark">3.45</span></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-appleMuted mt-2 px-6"><span>Sem 3</span><span>Sem 4</span><span>Sem 5</span></div>
                    </div>
                    <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-appleDark">Analisis Kompetensi</h4>
                        <div class="p-4 bg-bone-light border border-bone-dark rounded-[16px] text-[11px] space-y-2 mt-2">
                            <div class="flex justify-between"><span>Pemrograman</span><span class="font-bold">Sangat Baik</span></div>
                            <div class="flex justify-between"><span>Basis Data</span><span class="font-bold">Cukup</span></div>
                            <div class="flex justify-between"><span>Jaringan Komputer</span><span class="font-bold">Baik</span></div>
                        </div>
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
                        <div class="w-20 h-20 bg-appleDark text-white rounded-full mx-auto flex items-center justify-center text-2xl font-bold shadow-sm">MH</div>
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
