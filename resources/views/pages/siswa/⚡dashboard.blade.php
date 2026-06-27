@php
    use App\Services\BebanCalculator;
    use Carbon\Carbon;

    $profile = $data['profile'];
    $initials = strtoupper(substr($profile['nama'] ?? '-', 0, 2));
    $status = $profile['weekly_status'] ?? BebanCalculator::LIGHT;
    $pageTitles = [
        'dashboard' => ['Dashboard Akademik', 'Ringkasan beban tugas, SKS, dan deadline terdekat.'],
        'calendar' => ['Kalender Akademik', 'Peta deadline tugas per tanggal.'],
        'monitoring' => ['Monitoring SKS', 'Distribusi SKS dan beban mata kuliah semester ini.'],
        'analytics' => ['Analitik Akademik', 'Performa, risiko, dan rekomendasi akademik.'],
        'notifications' => ['Notifikasi', 'Pemberitahuan penting terkait aktivitas akademik.'],
        'profile' => ['Profil Mahasiswa', 'Identitas dan ringkasan akademik mahasiswa.'],
    ];
    [$pageTitle, $pageDescription] = $pageTitles[$currentTab] ?? $pageTitles['dashboard'];
    $navItems = [
        'dashboard' => ['Dashboard', 'M4 6h6v6H4V6Zm10 0h6v6h-6V6ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z'],
        'calendar' => ['Kalender', 'M8 3v4m8-4v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z'],
        'monitoring' => ['Monitoring SKS', 'M5 19V9m7 10V5m7 14v-7M3 19h18'],
        'analytics' => ['Analitik', 'M4 19V5m0 14h16M7 15l3-3 3 2 5-7'],
        'notifications' => ['Notifikasi', 'M15 17h5l-1.5-2V11a6.5 6.5 0 0 0-13 0v4L4 17h5m6 0a3 3 0 0 1-6 0'],
        'profile' => ['Profil', 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0'],
    ];
    $card = 'bg-white border border-bone-dark rounded-2xl shadow-sm';
    $muted = 'text-appleMuted';
@endphp

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-bone-light text-appleDark font-sans selection:bg-appleDark selection:text-white">
    <div class="grid min-h-screen lg:grid-cols-[16rem_1fr]">
        <aside class="border-b border-bone-dark bg-white/85 backdrop-blur lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between gap-4 px-5 py-4 lg:block lg:px-6 lg:py-6">
                <div>
                    <p class="text-xl font-bold tracking-tight">EduTrack</p>
                    <p class="text-[10px] font-bold uppercase tracking-widest {{ $muted }}">Siswa</p>
                </div>
                <form method="POST" action="{{ route('siswa.logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-bold text-appleRed">Keluar</button>
                </form>
            </div>

            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:flex-col lg:overflow-visible lg:px-4 lg:pb-0">
                @foreach($navItems as $tab => [$label, $path])
                    <a href="{{ route('siswa.dashboard', ['tab' => $tab]) }}"
                       class="flex shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $currentTab === $tab ? 'bg-appleDark text-white shadow-sm' : 'text-appleMuted hover:bg-bone hover:text-appleDark' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="{{ $path }}" /></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto hidden p-4 lg:block">
                <div class="mb-4 rounded-2xl bg-bone p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-appleDark text-xs font-bold text-white">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">{{ $profile['nama'] }}</p>
                            <p class="text-[11px] {{ $muted }}">{{ $profile['nim'] }}</p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('siswa.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-xl border border-red-100 px-3 py-2 text-sm font-bold text-appleRed hover:bg-red-50">Keluar Sesi</button>
                </form>
            </div>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-10 border-b border-bone-dark bg-bone-light/90 px-5 py-4 backdrop-blur lg:px-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ $pageTitle }}</h1>
                        <p class="mt-1 text-sm {{ $muted }}">{{ $pageDescription }}</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-white px-3 py-2 shadow-sm ring-1 ring-bone-dark">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-appleDark text-xs font-bold text-white">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold">{{ $profile['nama'] }}</p>
                            <p class="text-[10px] {{ $muted }}">Semester {{ $profile['semester'] }} · {{ $profile['prodi'] }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-6 p-5 pb-12 lg:p-8">
                @if($currentTab === 'dashboard')
                    @if(in_array($status, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true))
                        <section class="rounded-2xl border border-orange-200 bg-orange-50 p-4">
                            <div class="flex gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-appleOrange">!</div>
                                <div>
                                    <h2 class="text-sm font-bold">Beban Akademik {{ $status === BebanCalculator::OVERLOAD ? 'Overload' : 'Tinggi' }}</h2>
                                    <p class="mt-1 text-xs text-appleDark/70">Prioritaskan deadline terdekat dan kurangi penumpukan tugas minggu ini.</p>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['Total SKS Aktif', $profile['sks_semester'], 'text-appleDark', 'SKS semester ini'],
                            ['Tugas Minggu Ini', $data['weekly_task_count'], 'text-appleDark', 'deadline aktif'],
                            ['Deadline 3 Hari', $data['deadline_terdekat'], 'text-appleOrange', 'perlu dipantau'],
                            ['Status Beban', $data['status_beban_label'], $data['status_beban_color'], 'minggu berjalan'],
                        ] as [$label, $value, $color, $caption])
                            <div class="{{ $card }} p-5">
                                <p class="text-xs font-bold uppercase tracking-wide {{ $muted }}">{{ $label }}</p>
                                <p class="mt-2 text-3xl font-bold {{ $color }}">{{ $value }}</p>
                                <p class="mt-1 text-[11px] {{ $muted }}">{{ $caption }}</p>
                            </div>
                        @endforeach
                    </section>

                    <section class="grid gap-6 xl:grid-cols-[1.4fr_.9fr]">
                        <div class="{{ $card }} p-6">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-bold">Distribusi Beban Mingguan</h2>
                                    <p class="mt-1 text-xs {{ $muted }}">Jumlah tugas berdasarkan hari deadline.</p>
                                </div>
                                <span class="rounded-full bg-bone px-3 py-1 text-xs font-bold {{ $muted }}">{{ $data['weekly_task_count'] }} tugas</span>
                            </div>
                            @php $maxDaily = max(1, collect($data['daily_workload'])->max('count') ?? 1); @endphp
                            <div class="flex h-52 items-end gap-3 border-b border-bone-dark pb-3">
                                @foreach($data['daily_workload'] as $day)
                                    @php $height = max(8, ((int) $day['count'] / $maxDaily) * 100); @endphp
                                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                                        <div class="flex h-40 w-full items-end rounded-xl bg-bone-light px-1">
                                            <div class="w-full rounded-lg" style="height: {{ $height }}%; background-color: {{ $day['color'] }}"></div>
                                        </div>
                                        <span class="text-[11px] font-bold {{ $muted }}">{{ $day['day'] }}</span>
                                        <span class="text-[10px] {{ $muted }}">{{ $day['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="{{ $card }} p-6">
                            <h2 class="text-sm font-bold">Tugas Mendatang</h2>
                            <div class="mt-4 space-y-3">
                                @forelse($data['tugas_mendatang'] as $tugas)
                                    <div class="rounded-xl border border-bone-dark p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold">{{ $tugas['judul'] }}</p>
                                                <p class="mt-1 text-xs {{ $muted }}">{{ $tugas['matkul'] }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-bone px-2 py-1 text-[10px] font-bold {{ $muted }}">{{ $tugas['sisa'] }}</span>
                                        </div>
                                        <p class="mt-2 text-[11px] {{ $muted }}">{{ $tugas['deadline'] }} · {{ $tugas['jam'] }}</p>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-bone p-4 text-sm {{ $muted }}">Belum ada tugas mendatang.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                @if($currentTab === 'calendar')
                    <section class="grid gap-6 xl:grid-cols-[1.45fr_.8fr]">
                        <div class="{{ $card }} p-5 sm:p-6">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <h2 class="text-sm font-bold">{{ Carbon::parse($data['month_start'])->translatedFormat('F Y') }}</h2>
                                <div class="flex gap-2">
                                    <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_previous']['month'], 'year' => $data['calendar_previous']['year']]) }}" class="rounded-full border border-bone-dark px-3 py-1 text-sm font-bold hover:bg-bone">‹</a>
                                    <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $data['calendar_next']['month'], 'year' => $data['calendar_next']['year']]) }}" class="rounded-full border border-bone-dark px-3 py-1 text-sm font-bold hover:bg-bone">›</a>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 border-b border-bone-dark pb-3 text-center text-[11px] font-bold uppercase {{ $muted }}">
                                <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-2 text-xs">
                                @php
                                    $monthStart = Carbon::parse($data['month_start']);
                                    $daysInMonth = Carbon::parse($data['month_end'])->day;
                                    $emptyCells = $monthStart->dayOfWeekIso === 7 ? 0 : $monthStart->dayOfWeekIso;
                                @endphp
                                @for($i = 0; $i < $emptyCells; $i++)
                                    <div class="min-h-16 rounded-xl bg-bone-light/40"></div>
                                @endfor
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $taskCount = count($data['monthly_tasks'][$day] ?? []);
                                        $dayStatus = BebanCalculator::forCount($taskCount);
                                        $colorClass = match($dayStatus) {
                                            BebanCalculator::LIGHT => 'bg-green-50 border-green-200',
                                            BebanCalculator::NORMAL => 'bg-amber-50 border-amber-200',
                                            BebanCalculator::HEAVY => 'bg-red-50 border-red-200',
                                            BebanCalculator::OVERLOAD => 'bg-red-100 border-red-300',
                                            default => 'bg-bone-light border-bone-dark',
                                        };
                                    @endphp
                                    <a href="{{ route('siswa.dashboard', ['tab' => 'calendar', 'month' => $monthStart->month, 'year' => $monthStart->year, 'day' => $day]) }}"
                                       class="{{ $colorClass }} {{ $data['selected_day'] === $day ? 'ring-2 ring-appleDark' : '' }} flex min-h-16 flex-col justify-between rounded-xl border p-2 transition hover:-translate-y-0.5">
                                        <span class="font-bold">{{ $day }}</span>
                                        @if($taskCount > 0)
                                            <span class="text-[10px] font-bold {{ $muted }}">{{ $taskCount }} Tugas</span>
                                        @endif
                                    </a>
                                @endfor
                            </div>
                        </div>

                        <div class="{{ $card }} p-6">
                            <h2 class="text-sm font-bold">Timeline Deadline</h2>
                            <p class="mt-1 text-xs {{ $muted }}">{{ Carbon::parse($data['selected_date'])->translatedFormat('l, d F Y') }}</p>
                            <div class="mt-5 space-y-4">
                                @forelse($data['selected_day_tasks'] as $task)
                                    <div class="flex gap-3">
                                        <span class="w-12 shrink-0 font-mono text-[11px] {{ $muted }}">{{ $task['jam'] }}</span>
                                        <div class="border-l border-bone-dark pl-3">
                                            <p class="text-sm font-bold">{{ $task['judul'] }}</p>
                                            <p class="mt-1 text-xs {{ $muted }}">{{ $task['matkul'] }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-bone p-4 text-sm {{ $muted }}">Tidak ada deadline pada tanggal ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                @if($currentTab === 'monitoring')
                    <section class="grid gap-4 md:grid-cols-3">
                        @foreach([
                            ['SKS Aktif', $profile['sks_semester'].' SKS', 'Semester '.$profile['semester'], 'text-appleDark'],
                            ['SKS Lulus', $profile['sks_lulus'].' SKS', 'Total akumulatif', 'text-appleGreen'],
                            ['IPK Kumulatif', $profile['ipk'], 'Nilai terakhir tercatat', 'text-appleDark'],
                        ] as [$label, $value, $caption, $color])
                            <div class="{{ $card }} p-5">
                                <p class="text-xs font-bold uppercase tracking-wide {{ $muted }}">{{ $label }}</p>
                                <p class="mt-2 text-3xl font-bold {{ $color }}">{{ $value }}</p>
                                <p class="mt-1 text-[11px] {{ $muted }}">{{ $caption }}</p>
                            </div>
                        @endforeach
                    </section>

                    <section class="{{ $card }} overflow-hidden">
                        <div class="border-b border-bone-dark p-5">
                            <h2 class="text-sm font-bold">Mata Kuliah Semester Ini</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-bone text-xs font-bold uppercase tracking-wide {{ $muted }}">
                                    <tr>
                                        <th class="px-5 py-3">Mata Kuliah</th>
                                        <th class="px-5 py-3 text-center">SKS</th>
                                        <th class="px-5 py-3 text-center">Total Tugas</th>
                                        <th class="px-5 py-3 text-center">Minggu Ini</th>
                                        <th class="px-5 py-3 text-center">Beban</th>
                                        <th class="px-5 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-bone-dark/70">
                                    @forelse($data['matakuliah'] as $mk)
                                        <tr class="hover:bg-bone-light/70">
                                            <td class="px-5 py-4">
                                                <p class="font-bold">{{ $mk['nama'] }}</p>
                                                <p class="mt-1 text-xs {{ $muted }}">{{ $mk['kode'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $muted }}">{{ $mk['sks'] }}</td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $muted }}">{{ $mk['tugas'] }}</td>
                                            <td class="px-5 py-4 text-center font-semibold {{ $muted }}">{{ $mk['tugas_minggu_ini'] }}</td>
                                            <td class="px-5 py-4 text-center"><span class="rounded-full border px-3 py-1 text-xs font-bold {{ $mk['beban_color'] }}">{{ $mk['beban'] }}</span></td>
                                            <td class="px-5 py-4 text-center text-xs font-bold text-blue-600">{{ $mk['status'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-5 py-8 text-center text-sm {{ $muted }}">Belum ada mata kuliah aktif.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if($currentTab === 'analytics')
                    <section class="grid gap-6 xl:grid-cols-[1fr_.9fr]">
                        <div class="{{ $card }} p-6">
                            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-sm font-bold">Prediksi Risiko Akademik</h2>
                                    <p class="mt-1 text-xs {{ $muted }}">Berdasarkan beban tugas dan performa akademik.</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-4xl font-bold text-appleOrange">{{ $data['risk_score'] }}%</p>
                                    <p class="text-xs font-bold uppercase tracking-wide {{ $muted }}">Risiko</p>
                                </div>
                            </div>
                            <div class="mt-6 h-3 overflow-hidden rounded-full bg-bone">
                                <div class="h-full rounded-full bg-appleOrange" style="width: {{ min(100, max(0, (int) $data['risk_score'])) }}%"></div>
                            </div>
                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-green-100 bg-green-50 p-3"><p class="text-xs font-bold text-appleGreen">Aman</p><p class="text-[11px] {{ $muted }}">0-40%</p></div>
                                <div class="rounded-xl border border-orange-100 bg-orange-50 p-3"><p class="text-xs font-bold text-appleOrange">Perlu Perhatian</p><p class="text-[11px] {{ $muted }}">40-70%</p></div>
                                <div class="rounded-xl border border-red-100 bg-red-50 p-3"><p class="text-xs font-bold text-appleRed">Risiko Tinggi</p><p class="text-[11px] {{ $muted }}">70-100%</p></div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-appleDark p-6 text-white shadow-sm">
                            <h2 class="text-sm font-bold">Rekomendasi Akademik</h2>
                            <div class="mt-4 space-y-3 text-sm">
                                <div class="rounded-xl bg-white/10 p-4">
                                    <p class="font-bold text-appleOrange">SKS Semester Depan</p>
                                    <p class="mt-1 text-xs text-white/75">Disarankan mengambil maksimal {{ $data['sks_recommendation']['sks'] }} SKS. {{ $data['sks_recommendation']['reason'] }}</p>
                                </div>
                                <div class="rounded-xl bg-white/10 p-4">
                                    <p class="font-bold">Prioritas Fokus</p>
                                    <p class="mt-1 text-xs text-white/75">{{ $data['risk_score'] >= 70 ? 'Kurangi penumpukan deadline dan konsultasikan beban dengan dosen PA.' : ($data['risk_score'] >= 40 ? 'Pantau mata kuliah dengan beban mingguan tertinggi.' : 'Beban akademik masih stabil, pertahankan ritme belajar.') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="{{ $card }} p-6">
                            <h2 class="text-sm font-bold">Tren Historis IPK</h2>
                            @if(count($data['ipk_history']))
                                @php $maxIpk = 4.0; @endphp
                                <div class="mt-6 flex h-44 items-end gap-4 border-b border-l border-bone-dark p-3">
                                    @foreach($data['ipk_history'] as $ipkEntry)
                                        @php $height = max(8, ((float) $ipkEntry['ipk'] / $maxIpk) * 100); @endphp
                                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                                            <span class="text-[10px] {{ $muted }}">{{ number_format($ipkEntry['ipk'], 2) }}</span>
                                            <div class="w-full rounded-t-lg bg-appleDark" style="height: {{ $height }}%"></div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 flex justify-between px-4 text-[10px] {{ $muted }}">
                                    @foreach($data['ipk_history'] as $ipkEntry)
                                        <span>{{ $ipkEntry['semester'] }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 rounded-xl bg-bone p-4 text-sm {{ $muted }}">Belum ada riwayat IPK.</p>
                            @endif
                        </div>

                        <div class="{{ $card }} p-6">
                            <h2 class="text-sm font-bold">Kompetensi Mata Kuliah</h2>
                            <div class="mt-5 space-y-4">
                                @forelse($data['competency'] as $skill)
                                    <div>
                                        <div class="flex justify-between gap-3 text-xs">
                                            <span class="font-bold">{{ $skill['nama'] }}</span>
                                            <span class="{{ $muted }}">{{ $skill['label'] }} · {{ $skill['score'] }}</span>
                                        </div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-bone"><div class="h-full rounded-full bg-blue-500" style="width: {{ min(100, max(0, (int) $skill['score'])) }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="rounded-xl bg-bone p-4 text-sm {{ $muted }}">Belum ada nilai tugas yang bisa dihitung menjadi kompetensi.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                @if($currentTab === 'notifications')
                    <section class="{{ $card }} divide-y divide-bone-dark/70 overflow-hidden">
                        @forelse($data['notifikasi'] as $notif)
                            <div class="flex gap-4 p-5 hover:bg-bone-light/70">
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notif['tipe'] === 'peringatan' ? 'bg-appleRed' : ($notif['tipe'] === 'pengingat' ? 'bg-appleOrange' : ($notif['tipe'] === 'sukses' ? 'bg-appleGreen' : 'bg-blue-500')) }}"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-sm font-bold">{{ $notif['judul'] }}</h2>
                                        @if($notif['unread'])<span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>@endif
                                    </div>
                                    <p class="mt-1 text-sm leading-relaxed {{ $muted }}">{{ $notif['desc'] }}</p>
                                    <p class="mt-2 font-mono text-[11px] {{ $muted }}">{{ $notif['waktu'] }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="p-6 text-sm {{ $muted }}">Belum ada notifikasi.</p>
                        @endforelse
                    </section>
                @endif

                @if($currentTab === 'profile')
                    <section class="grid gap-6 lg:grid-cols-[.85fr_1.35fr]">
                        <div class="{{ $card }} p-6 text-center">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-appleDark text-2xl font-bold text-white">{{ $initials }}</div>
                            <h2 class="mt-4 text-lg font-bold">{{ $profile['nama'] }}</h2>
                            <p class="mt-1 text-sm {{ $muted }}">NIM: {{ $profile['nim'] }}</p>
                            <div class="mt-6 space-y-3 border-t border-bone-dark pt-5 text-left text-sm">
                                <div><p class="text-xs font-bold uppercase {{ $muted }}">Program Studi</p><p class="mt-1 font-semibold">{{ $profile['prodi'] }}</p></div>
                                <div><p class="text-xs font-bold uppercase {{ $muted }}">Email</p><p class="mt-1 font-semibold break-words">{{ $profile['email'] }}</p></div>
                                <div><p class="text-xs font-bold uppercase {{ $muted }}">Angkatan</p><p class="mt-1 font-semibold">Semester {{ $profile['semester'] }} · Tahun {{ $profile['angkatan'] }}</p></div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="{{ $card }} p-6">
                                <h2 class="text-sm font-bold">Ringkasan Akademik</h2>
                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    @foreach([
                                        ['IPK', $profile['ipk']],
                                        ['SKS Lulus', $profile['sks_lulus']],
                                        ['SKS Kontrak', $profile['sks_semester']],
                                        ['Semester', $profile['semester']],
                                    ] as [$label, $value])
                                        <div class="rounded-xl bg-bone p-4 text-center">
                                            <p class="text-[11px] font-bold uppercase {{ $muted }}">{{ $label }}</p>
                                            <p class="mt-1 text-lg font-bold">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-5 flex items-center gap-3 border-t border-bone-dark pt-5">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bone text-xs font-bold {{ $muted }}">PA</div>
                                    <div>
                                        <p class="text-xs font-bold uppercase {{ $muted }}">Dosen Pembimbing Akademik</p>
                                        <p class="mt-1 text-sm font-bold">{{ $profile['dosen_pa'] }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="{{ $card }} p-6">
                                <h2 class="text-sm font-bold">Status Akun</h2>
                                <div class="mt-4 divide-y divide-bone-dark text-sm">
                                    <div class="flex justify-between py-3"><span class="{{ $muted }}">Role</span><span class="font-bold">Siswa</span></div>
                                    <div class="flex justify-between py-3"><span class="{{ $muted }}">Status Akademik</span><span class="font-bold">Aktif</span></div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
