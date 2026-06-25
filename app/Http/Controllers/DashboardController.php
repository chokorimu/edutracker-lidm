<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('pages.admin.⚡dashboard', [
            'user' => Auth::guard('admin')->user(),
        ]);
    }

    public function dosen(): View
    {
        return view('pages.dosen.⚡dashboard', [
            'user' => Auth::guard('dosen')->user(),
        ]);
    }

    public function siswa(Request $request): View
    {
        $tabs = ['dashboard', 'calendar', 'monitoring', 'analytics', 'notifications', 'profile'];
        $currentTab = $request->query('tab', 'dashboard');

        if (! in_array($currentTab, $tabs, true)) {
            $currentTab = 'dashboard';
        }

        return view('pages.siswa.⚡dashboard', [
            'currentTab' => $currentTab,
            'data' => $this->siswaDashboardData($request),
        ]);
    }

    public function logoutAdmin(Request $request): RedirectResponse
    {
        return $this->logout($request, 'admin');
    }

    public function logoutDosen(Request $request): RedirectResponse
    {
        return $this->logout($request, 'dosen');
    }

    public function logoutSiswa(Request $request): RedirectResponse
    {
        return $this->logout($request, 'siswa');
    }

    public function logoutProdi(Request $request): RedirectResponse
    {
        return $this->logout($request, 'prodi');
    }

    private function logout(Request $request, string $guard): RedirectResponse
    {
        Auth::guard($guard)->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }

    private function siswaDashboardData(?Request $request = null): array
    {
        $user = Auth::guard('siswa')->user();
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        if (! $user) {
            return $this->emptySiswaData();
        }

        $nim = $user->nim;
        $angkatan = $this->extractAngkatan($nim);

        // IPK terakhir
        $latestIpk = $user->ipkHistory()->latest('semester')->first();

        // Total SKS lulus (KRS status selesai)
        $sksLulus = MataKuliah::whereIn('id', function ($q) use ($user) {
            $q->select('mata_kuliah_id')
                ->from('krs')
                ->where('siswa_id', $user->id)
                ->where('status', 'selesai');
        })->sum('sks');

        // Total SKS semester ini
        $sksSemester = MataKuliah::whereIn('id', function ($q) use ($user) {
            $q->select('mata_kuliah_id')
                ->from('krs')
                ->where('siswa_id', $user->id)
                ->where('semester', $user->semester);
        })->sum('sks');

        // Dosen PA terakhir
        $dosenPa = $user->dosenPa()->with('dosen')->latest()->first();
        $dosenPaName = $dosenPa?->dosen?->name ?? '-';

        // Mata kuliah semester ini
        $krsList = $user->krs()
            ->with('mataKuliah')
            ->where('semester', $user->semester)
            ->get();

        $matakuliah = $krsList->map(function ($krs) {
            $mk = $krs->mataKuliah;
            if (! $mk) {
                return null;
            }

            $tugasCount = $mk->tugas()->count();
            $weeklyTugasCount = $mk->tugas()
                ->whereBetween('deadline', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->count();
            $statusBeban = BebanCalculator::forCount($weeklyTugasCount);

            return [
                'nama' => $mk->nama,
                'kode' => $mk->kode,
                'sks' => $mk->sks,
                'tugas' => $tugasCount,
                'tugas_minggu_ini' => $weeklyTugasCount,
                'beban' => BebanCalculator::label($statusBeban),
                'beban_status' => $statusBeban,
                'beban_color' => BebanCalculator::colorClass($statusBeban),
                'status' => $krs->status === 'selesai' ? 'Selesai' : 'Aktif',
            ];
        })->filter()->values()->toArray();

        // Tugas mendatang (4 terdekat)
        $tugasMendatang = Tugas::whereHas('mataKuliah.krs', function ($q) use ($user) {
            $q->where('siswa_id', $user->id);
        })
            ->whereDate('deadline', '>=', now()->startOfDay())
            ->with('mataKuliah')
            ->orderBy('deadline')
            ->take(4)
            ->get()
            ->map(function ($t) {
                $diffDays = (int) now()->startOfDay()->diffInDays($t->deadline, false);

                if ($diffDays <= 0) {
                    $status = 'critical';
                    $sisa = 'Hari ini';
                } elseif ($diffDays <= 1) {
                    $status = 'critical';
                    $sisa = $diffDays.' hari lagi';
                } elseif ($diffDays <= 3) {
                    $status = 'warning';
                    $sisa = $diffDays.' hari lagi';
                } else {
                    $status = 'safe';
                    $sisa = $diffDays.' hari lagi';
                }

                return [
                    'judul' => $t->nama,
                    'matkul' => $t->mataKuliah?->nama ?? '-',
                    'deadline' => $t->deadline ? Carbon::parse($t->deadline)->translatedFormat('j F Y') : '-',
                    'deadline_iso' => $t->deadline ? Carbon::parse($t->deadline)->toDateString() : null,
                    'sisa' => $sisa,
                    'jam' => $t->deadline ? Carbon::parse($t->deadline)->format('H:i') : '-',
                    'status' => $status,
                ];
            })
            ->toArray();

        // Notifikasi (5 terbaru)
        $notifikasi = $user->notifikasi()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($n) {
                $tipeMap = [
                    'peringatan' => 'peringatan',
                    'warning' => 'peringatan',
                    'pengingat' => 'pengingat',
                    'reminder' => 'pengingat',
                    'sukses' => 'sukses',
                    'success' => 'sukses',
                    'info' => 'informasi',
                    'informasi' => 'informasi',
                ];

                return [
                    'tipe' => $tipeMap[$n->tipe] ?? 'informasi',
                    'judul' => $n->judul,
                    'desc' => $n->pesan,
                    'waktu' => $this->diffForHumansId($n->created_at),
                    'unread' => ! $n->is_read,
                ];
            })
            ->toArray();

        // Data for daily workload chart
        $dailyWorkload = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $count = Tugas::whereHas('mataKuliah.krs', function ($q) use ($user) {
                $q->where('siswa_id', $user->id);
            })
                ->whereDate('deadline', $date->toDateString())
                ->count();

            $dailyWorkload[] = [
                'day' => $date->translatedFormat('D'),
                'count' => $count,
                'color' => $count >= 3 ? '#EF4444' : ($count >= 2 ? '#F59E0B' : '#10B981'),
            ];
        }

        // Weekly overload status
        $weeklyTaskCount = Tugas::whereHas('mataKuliah.krs', function ($q) use ($user) {
            $q->where('siswa_id', $user->id);
        })
            ->whereBetween('deadline', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->count();
        $weeklyStatus = BebanCalculator::forCount($weeklyTaskCount);

        $riskScore = BebanCalculator::riskScoreForStudent($user, $startOfWeek, $endOfWeek);
        $sksRecommendation = BebanCalculator::recommendSks($user, $riskScore);

        // Deadline terdekat (tugas < 4 hari)
        $deadlineTerdekat = Tugas::whereHas('mataKuliah.krs', function ($q) use ($user) {
            $q->where('siswa_id', $user->id);
        })
            ->whereBetween('deadline', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->count();

        // Label bahasa untuk status beban
        $statusBebanLabel = match ($weeklyStatus) {
            BebanCalculator::LIGHT => 'Ringan',
            BebanCalculator::NORMAL => 'Normal',
            BebanCalculator::HEAVY => 'Berat',
            BebanCalculator::OVERLOAD => 'Overload',
            default => 'Ringan',
        };

        // Warna status beban
        $statusBebanColor = match ($weeklyStatus) {
            BebanCalculator::LIGHT => 'text-appleGreen',
            BebanCalculator::NORMAL => 'text-appleOrange',
            BebanCalculator::HEAVY => 'text-appleRed',
            BebanCalculator::OVERLOAD => 'text-appleRed',
            default => 'text-appleGreen',
        };

        // IPK history (for chart)
        $ipkHistory = $user->ipkHistory()
            ->orderBy('semester')
            ->get()
            ->map(fn ($h) => ['semester' => 'Sem '.$h->semester, 'ipk' => (float) $h->ipk])
            ->toArray();
        $competency = BebanCalculator::competencyByCourse($user);

        // Monthly calendar — with optional month/year nav from query
        $calendarMonth = $request?->query('month');
        $calendarYear = $request?->query('year');
        if ($calendarMonth && $calendarYear) {
            $monthStart = Carbon::createFromDate((int) $calendarYear, (int) $calendarMonth, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
        } else {
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
        }
        $monthlyTasks = Tugas::whereHas('mataKuliah.krs', function ($q) use ($user) {
            $q->where('siswa_id', $user->id);
        })
            ->with('mataKuliah')
            ->whereBetween('deadline', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('deadline')
            ->get()
            ->groupBy(function ($t) {
                return Carbon::parse($t->deadline)->day;
            });
        $selectedDay = (int) ($request?->query('day', now()->day));
        if ($selectedDay < 1 || $selectedDay > $monthEnd->day) {
            $selectedDay = now()->day;
        }
        $selectedDate = $monthStart->copy()->day($selectedDay);
        $selectedDayTasks = $monthlyTasks->get($selectedDay, collect())
            ->map(fn ($task) => [
                'judul' => $task->nama,
                'matkul' => $task->mataKuliah?->nama ?? '-',
                'jam' => Carbon::parse($task->deadline)->format('H:i'),
                'status' => Carbon::parse($task->deadline)->isPast() ? 'lewat' : 'aktif',
            ])
            ->values()
            ->toArray();
        $previousMonth = $monthStart->copy()->subMonth();
        $nextMonth = $monthStart->copy()->addMonth();

        return [
            'profile' => [
                'nim' => $nim,
                'nama' => $user->name,
                'email' => $user->email,
                'prodi' => $user->prodi ?? '-',
                'semester' => $user->semester ?? 1,
                'angkatan' => $angkatan,
                'ipk' => $latestIpk?->ipk ?? 0.0,
                'sks_lulus' => $sksLulus ?: ($latestIpk?->total_sks ?? 0),
                'sks_semester' => $sksSemester ?: 0,
                'dosen_pa' => $dosenPaName,
                'weekly_status' => $weeklyStatus,
            ],
            'matakuliah' => $matakuliah,
            'tugas_mendatang' => $tugasMendatang,
            'notifikasi' => $notifikasi,
            'daily_workload' => $dailyWorkload,
            'monthly_tasks' => $monthlyTasks,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'selected_day' => $selectedDay,
            'selected_date' => $selectedDate,
            'selected_day_tasks' => $selectedDayTasks,
            'calendar_previous' => ['month' => $previousMonth->month, 'year' => $previousMonth->year],
            'calendar_next' => ['month' => $nextMonth->month, 'year' => $nextMonth->year],
            'weekly_task_count' => $weeklyTaskCount,
            'deadline_terdekat' => $deadlineTerdekat,
            'status_beban_label' => $statusBebanLabel,
            'status_beban_color' => $statusBebanColor,
            'ipk_history' => $ipkHistory,
            'risk_score' => $riskScore,
            'sks_recommendation' => $sksRecommendation,
            'competency' => $competency,
        ];
    }

    private function extractAngkatan(?string $nim): int
    {
        if ($nim && preg_match('/^(\d{2})/', $nim, $m)) {
            $year = (int) $m[1];

            return $year > 50 ? 1900 + $year : 2000 + $year;
        }

        return (int) now()->format('Y');
    }

    private function diffForHumansId($date): string
    {
        if (! $date) {
            return '-';
        }

        $now = now();
        $diffDays = (int) $date->diffInDays($now);
        $diffHours = (int) $date->diffInHours($now);
        $diffMinutes = (int) $date->diffInMinutes($now);

        if ($diffDays > 0) {
            return $diffDays.' hari yang lalu';
        }
        if ($diffHours > 0) {
            return $diffHours.' jam yang lalu';
        }
        if ($diffMinutes > 0) {
            return $diffMinutes.' menit yang lalu';
        }

        return 'Baru saja';
    }

    private function emptySiswaData(): array
    {
        $nowYear = (int) now()->format('Y');

        return [
            'profile' => [
                'nim' => '-', 'nama' => '-', 'email' => '-',
                'prodi' => '-', 'semester' => 1, 'angkatan' => $nowYear,
                'ipk' => 0.0, 'sks_lulus' => 0, 'sks_semester' => 0, 'dosen_pa' => '-',
            ],
            'matakuliah' => [],
            'tugas_mendatang' => [],
            'notifikasi' => [],
        ];
    }
}
