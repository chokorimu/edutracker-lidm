<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\UserSiswa;
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

        if (! $user) {
            return $this->emptySiswaData();
        }

        $now = now();
        $allCourseIds = $user->krs()
            ->pluck('mata_kuliah_id')
            ->filter()
            ->unique()
            ->values();
        [$startOfWeek, $endOfWeek] = $this->resolveWorkloadWeek($allCourseIds, $now);

        $workloadData = $this->buildWorkloadData($allCourseIds, $startOfWeek, $endOfWeek);

        return array_merge(
            $this->buildProfileData($user, $workloadData['weekly_status_code']),
            $this->buildMatakuliahData($user, $startOfWeek, $endOfWeek),
            $this->buildTugasData($allCourseIds),
            $this->buildNotifikasiData($user),
            $workloadData['payload'],
            $this->buildCalendarData($request, $allCourseIds, $now),
            $this->buildAnalyticsData($user, $allCourseIds, $startOfWeek, $endOfWeek),
        );
    }

    private function buildProfileData(UserSiswa $user, string $weeklyStatus): array
    {
        $nim = $user->nim;
        $angkatan = $this->extractAngkatan($nim);
        $latestIpk = $user->ipkHistory()->latest('semester')->first();
        $dosenPa = $user->dosenPa()->with('dosen')->latest()->first();
        $dosenPaName = $dosenPa?->dosen?->name ?? '-';

        $krsList = $user->krs()
            ->with('mataKuliah')
            ->where('semester', $user->semester)
            ->get();

        $sksSemester = $krsList->sum(fn ($krs) => (int) ($krs->mataKuliah?->sks ?? 0));
        $sksLulus = (int) $user->krs()
            ->where('status', 'selesai')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
            ->sum('mata_kuliah.sks');

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
        ];
    }

    private function buildMatakuliahData(UserSiswa $user, Carbon $startOfWeek, Carbon $endOfWeek): array
    {
        $krsList = $user->krs()
            ->with('mataKuliah')
            ->where('semester', $user->semester)
            ->get();

        $currentCourseIds = $krsList->pluck('mata_kuliah_id')->filter()->values();
        $taskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $currentCourseIds)
            ->selectRaw('mata_kuliah_id, COUNT(*) as aggregate')
            ->groupBy('mata_kuliah_id')
            ->pluck('aggregate', 'mata_kuliah_id');

        $weeklyTaskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $currentCourseIds)
            ->whereBetween('deadline', [$startOfWeek, $endOfWeek])
            ->selectRaw('mata_kuliah_id, COUNT(*) as aggregate')
            ->groupBy('mata_kuliah_id')
            ->pluck('aggregate', 'mata_kuliah_id');

        $matakuliah = $krsList->map(function ($krs) use ($user, $taskCountsByCourse, $weeklyTaskCountsByCourse) {
            $mk = $krs->mataKuliah;
            if (! $mk) {
                return null;
            }

            $tugasCount = (int) $taskCountsByCourse->get($mk->id, 0);
            $weeklyTugasCount = (int) $weeklyTaskCountsByCourse->get($mk->id, 0);
            $statusBeban = BebanCalculator::forCount($weeklyTugasCount);
            $tugasWithNilai = Tugas::where('mata_kuliah_id', $krs->mata_kuliah_id)
                ->with(['nilaiTugas' => fn ($query) => $query->where('siswa_id', $user->id)])
                ->orderBy('deadline')
                ->get()
                ->map(fn ($tugas) => [
                    'nama' => $tugas->nama,
                    'bobot' => $tugas->bobot,
                    'deadline' => $tugas->deadline,
                    'nilai' => $tugas->nilaiTugas->first()?->nilai,
                ]);

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
                'tugas_nilai' => $tugasWithNilai,
            ];
        })->filter()->values()->toArray();

        return [
            'matakuliah' => $matakuliah,
        ];
    }

    private function buildTugasData($allCourseIds): array
    {
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

        return [
            'tugas_mendatang' => $tugasMendatang,
        ];
    }

    private function buildNotifikasiData(UserSiswa $user): array
    {
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

        return [
            'notifikasi' => $notifikasi,
        ];
    }

    private function buildWorkloadData($allCourseIds, Carbon $startOfWeek, Carbon $endOfWeek): array
    {
        $weeklyTasks = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$startOfWeek, $endOfWeek])
            ->get(['deadline']);

        $weeklyTaskCount = $weeklyTasks->count();
        $weeklyStatus = BebanCalculator::forCount($weeklyTaskCount);
        $barColor = match ($weeklyStatus) {
            BebanCalculator::NORMAL => '#F59E0B',
            BebanCalculator::HEAVY => '#EF4444',
            BebanCalculator::OVERLOAD => '#B91C1C',
            default => '#10B981',
        };
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
                'color' => $count > 0 ? $barColor : '#D1D5DB',
            ];
        }

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

        return [
            'weekly_status_code' => $weeklyStatus,
            'payload' => [
                'daily_workload' => $dailyWorkload,
                'workload_week_label' => $startOfWeek->translatedFormat('d M').' - '.$endOfWeek->translatedFormat('d M Y'),
                'weekly_task_count' => $weeklyTaskCount,
                'status_beban_label' => $statusBebanLabel,
                'status_beban_color' => $statusBebanColor,
                'weekly_status' => [
                    'label' => $statusBebanLabel,
                    'color' => $statusBebanColor,
                    'count' => $weeklyTaskCount,
                ],
            ],
        ];
    }

    private function resolveWorkloadWeek($allCourseIds, Carbon $now): array
    {
        $defaultStart = $now->copy()->startOfWeek();
        $defaultEnd = $now->copy()->endOfWeek();

        if ($allCourseIds->isEmpty()) {
            return [$defaultStart, $defaultEnd];
        }

        $tasks = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$defaultStart, $now->copy()->addWeeks(8)->endOfWeek()])
            ->orderBy('deadline')
            ->get(['deadline']);

        if ($tasks->isEmpty()) {
            return [$defaultStart, $defaultEnd];
        }

        $week = $tasks
            ->groupBy(fn ($task) => Carbon::parse($task->deadline)->startOfWeek()->toDateString())
            ->sortByDesc(fn ($tasks) => $tasks->count())
            ->keys()
            ->first();

        $start = Carbon::parse($week)->startOfWeek();

        return [$start, $start->copy()->endOfWeek()];
    }

    private function buildCalendarData(?Request $request, $allCourseIds, Carbon $now): array
    {
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
            ->whereBetween('deadline', [$monthStart, $monthEnd])
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

        $monthlyTasks = $monthlyTasks
            ->map(fn ($tasks) => $tasks->map(fn ($task) => [
                'judul' => $task->nama,
                'matkul' => $task->mataKuliah?->nama ?? '-',
                'jam' => Carbon::parse($task->deadline)->format('H:i'),
                'status' => Carbon::parse($task->deadline)->isPast() ? 'lewat' : 'aktif',
            ])->values()->toArray())
            ->toArray();

        $previousMonth = $monthStart->copy()->subMonth();
        $nextMonth = $monthStart->copy()->addMonth();

        return [
            'monthly_tasks' => $monthlyTasks,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'selected_day' => $selectedDay,
            'selected_date' => $selectedDate,
            'selected_day_tasks' => $selectedDayTasks,
            'calendar_previous' => ['month' => $previousMonth->month, 'year' => $previousMonth->year],
            'calendar_next' => ['month' => $nextMonth->month, 'year' => $nextMonth->year],
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

    private function buildAnalyticsData(UserSiswa $user, $allCourseIds, Carbon $startOfWeek, Carbon $endOfWeek): array
    {
        $riskScore = BebanCalculator::riskScoreForStudent($user, $startOfWeek, $endOfWeek);
        $sksRecommendation = BebanCalculator::recommendSks($user, $riskScore);
        $deadlineTerdekat = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->count();

        $ipkHistory = $user->ipkHistory()
            ->orderBy('semester')
            ->get()
            ->map(fn ($h) => ['semester' => 'Sem '.$h->semester, 'ipk' => (float) $h->ipk])
            ->toArray();

        return [
            'risk_score' => $riskScore,
            'sks_recommendation' => $sksRecommendation,
            'deadline_terdekat' => $deadlineTerdekat,
            'ipk_history' => $ipkHistory,
            'competency' => BebanCalculator::competencyByCourse($user),
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
        return $date->locale('id')->diffForHumans();
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
            'monthly_tasks' => [],
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
