<?php

namespace App\Http\Controllers;

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

    public function siswa(Request $request): View|RedirectResponse
    {
        $user = Auth::guard('siswa')->user();
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

    public function savePreferences(Request $request): RedirectResponse
    {
        $user = Auth::guard('siswa')->user();
        $validated = $request->validate([
            'preferences' => 'array',
        ]);

        $user->update([
            'notification_preferences' => $validated['preferences'] ?? [],
        ]);

        return back()->with('status', 'Pengaturan notifikasi disimpan.');
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

        $latestIpk = $user->ipkHistory()->latest('semester')->first();

        $dosenPa = $user->dosenPa()->with('dosen')->latest()->first();
        $dosenPaName = $dosenPa?->dosen?->name ?? '-';

        $allCourseIds = $user->krs()
            ->pluck('mata_kuliah_id')
            ->filter()
            ->unique()
            ->values();

        $krsList = $user->krs()
            ->with('mataKuliah')
            ->where('semester', $user->semester)
            ->get();

        $currentCourseIds = $krsList->pluck('mata_kuliah_id')->filter()->values();
        $sksSemester = $krsList->sum(fn ($krs) => (int) ($krs->mataKuliah?->sks ?? 0));
        $sksLulus = (int) $user->krs()
            ->where('status', 'selesai')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
            ->sum('mata_kuliah.sks');

        $taskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $currentCourseIds)
            ->selectRaw('mata_kuliah_id, COUNT(*) as aggregate')
            ->groupBy('mata_kuliah_id')
            ->pluck('aggregate', 'mata_kuliah_id');

        $weeklyTaskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $currentCourseIds)
            ->whereBetween('deadline', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->selectRaw('mata_kuliah_id, COUNT(*) as aggregate')
            ->groupBy('mata_kuliah_id')
            ->pluck('aggregate', 'mata_kuliah_id');

        $matakuliah = $krsList->map(function ($krs) use ($taskCountsByCourse, $weeklyTaskCountsByCourse) {
            $mk = $krs->mataKuliah;
            if (! $mk) {
                return null;
            }

            $tugasCount = (int) $taskCountsByCourse->get($mk->id, 0);
            $weeklyTugasCount = (int) $weeklyTaskCountsByCourse->get($mk->id, 0);
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

        $tugasMendatang = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
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

        $weeklyTasks = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get(['deadline']);
        $weeklyTaskCount = $weeklyTasks->count();
        $dailyTaskCounts = $weeklyTasks
            ->groupBy(fn ($task) => Carbon::parse($task->deadline)->toDateString())
            ->map->count();

        $dailyWorkload = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $count = (int) $dailyTaskCounts->get($date->toDateString(), 0);

            $dailyWorkload[] = [
                'day' => $date->translatedFormat('D'),
                'count' => $count,
                'color' => $count >= 3 ? '#EF4444' : ($count >= 2 ? '#F59E0B' : '#10B981'),
            ];
        }

        $weeklyStatus = BebanCalculator::forCount($weeklyTaskCount);

        $riskScore = BebanCalculator::riskScoreForStudent($user, $startOfWeek, $endOfWeek);
        $sksRecommendation = BebanCalculator::recommendSks($user, $riskScore);

        $deadlineTerdekat = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->count();

        $statusBebanLabel = match ($weeklyStatus) {
            BebanCalculator::LIGHT => 'Ringan',
            BebanCalculator::NORMAL => 'Normal',
            BebanCalculator::HEAVY => 'Berat',
            BebanCalculator::OVERLOAD => 'Overload',
            default => 'Ringan',
        };

        $statusBebanColor = match ($weeklyStatus) {
            BebanCalculator::LIGHT => 'text-appleGreen',
            BebanCalculator::NORMAL => 'text-appleOrange',
            BebanCalculator::HEAVY => 'text-appleRed',
            BebanCalculator::OVERLOAD => 'text-appleRed',
            default => 'text-appleGreen',
        };

        $ipkHistory = $user->ipkHistory()
            ->orderBy('semester')
            ->get()
            ->map(fn ($h) => ['semester' => 'Sem '.$h->semester, 'ipk' => (float) $h->ipk])
            ->toArray();
        $competency = BebanCalculator::competencyByCourse($user);

        $calendarMonth = $request?->query('month');
        $calendarYear = $request?->query('year');
        if ($calendarMonth && $calendarYear) {
            $monthStart = Carbon::createFromDate((int) $calendarYear, (int) $calendarMonth, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
        } else {
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
        }
        $monthlyTasks = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
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
                'ipk' => $latestIpk?->ipk ?? '-',
                'sks_lulus' => $sksLulus,
                'sks_semester' => $sksSemester,
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
            'status_beban_label' => $statusBebanLabel,
            'status_beban_color' => $statusBebanColor,
            'weekly_status' => [
                'label' => $statusBebanLabel,
                'color' => $statusBebanColor,
                'count' => $weeklyTaskCount,
            ],
            'risk_score' => $riskScore,
            'sks_recommendation' => $sksRecommendation,
            'deadline_terdekat' => $deadlineTerdekat,
            'ipk_history' => $ipkHistory,
            'competency' => $competency,
            'calendar' => [
                'month' => $monthStart->translatedFormat('F'),
                'year' => $monthStart->year,
                'selected_day' => $selectedDay,
                'selected_date' => $selectedDate->toDateString(),
                'days_in_month' => $monthEnd->day,
                'first_day_of_month' => $monthStart->dayOfWeekIso,
                'day_tasks' => $selectedDayTasks,
                'prev_month' => $previousMonth->format('m'),
                'prev_year' => $previousMonth->year,
                'next_month' => $nextMonth->format('m'),
                'next_year' => $nextMonth->year,
            ],
        ];
    }

    private function extractAngkatan(string $nim): string
    {
        $prefix = substr($nim, 0, 2);
        if (! is_numeric($prefix)) {
            return '-';
        }

        $year = (int) $prefix + 2000;

        return $year >= 2000 && $year <= 2100 ? (string) $year : '-';
    }

    private function diffForHumansId($date): string
    {
        return $date->diffForHumans();
    }

    private function emptySiswaData(): array
    {
        return [
            'profile' => [
                'nim' => '-', 'nama' => '-', 'email' => '-', 'prodi' => '-', 'semester' => 1, 'angkatan' => '-', 'ipk' => '-', 'sks_lulus' => 0, 'sks_semester' => 0, 'dosen_pa' => '-', 'weekly_status' => BebanCalculator::LIGHT,
            ],
            'matakuliah' => [],
            'tugas_mendatang' => [],
            'notifikasi' => [],
            'daily_workload' => [],
            'monthly_tasks' => collect(),
            'month_start' => now()->startOfMonth(),
            'month_end' => now()->endOfMonth(),
            'selected_day' => now()->day,
            'selected_date' => now(),
            'selected_day_tasks' => [],
            'calendar_previous' => ['month' => now()->subMonth()->month, 'year' => now()->subMonth()->year],
            'calendar_next' => ['month' => now()->addMonth()->month, 'year' => now()->addMonth()->year],
            'weekly_task_count' => 0,
            'status_beban_label' => 'Tidak ada data',
            'status_beban_color' => 'text-gray-400',
            'weekly_status' => ['label' => 'Tidak ada data', 'color' => 'text-gray-400', 'count' => 0],
            'risk_score' => 0,
            'sks_recommendation' => ['sks' => 0, 'reason' => '-'],
            'deadline_terdekat' => 0,
            'ipk_history' => [],
            'competency' => [],
            'calendar' => [
                'month' => '-', 'year' => '-', 'selected_day' => 1, 'selected_date' => '-', 'days_in_month' => 0, 'first_day_of_month' => 0, 'day_tasks' => [], 'prev_month' => '-', 'prev_year' => '-', 'next_month' => '-', 'next_year' => '-',
            ],
        ];
    }
}
