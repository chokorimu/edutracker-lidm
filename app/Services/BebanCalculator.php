<?php

namespace App\Services;

use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\Tugas;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BebanCalculator
{
    public const LIGHT = 'ringan';

    public const NORMAL = 'normal';

    public const HEAVY = 'berat';

    public const OVERLOAD = 'overload';

    public const URGENT_TASK_DAYS = 3;

    public static function severity(string $status): int
    {
        return match ($status) {
            self::NORMAL => 1,
            self::HEAVY => 2,
            self::OVERLOAD => 3,
            default => 0,
        };
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::NORMAL => 'Normal',
            self::HEAVY => 'Berat',
            self::OVERLOAD => 'Overload',
            default => 'Ringan',
        };
    }

    public static function colorClass(string $status): string
    {
        return match ($status) {
            self::NORMAL => 'bg-amber-50 text-appleOrange border-amber-200',
            self::HEAVY => 'bg-red-50 text-appleRed border-red-200',
            self::OVERLOAD => 'bg-red-100 text-appleRed border-red-300',
            default => 'bg-green-50 text-appleGreen border-green-200',
        };
    }

    public static function forCount(int $count): string
    {
        return match (true) {
            $count <= 1 => self::LIGHT,
            $count === 2 => self::NORMAL,
            $count === 3 => self::HEAVY,
            default => self::OVERLOAD,
        };
    }

    public static function weeklyLoadForCourse(int $mataKuliahId, $weekStart, $weekEnd): Collection
    {
        $krsInCourse = Krs::where('mata_kuliah_id', $mataKuliahId)->with('siswa')->get();
        $siswaIds = $krsInCourse->pluck('siswa_id');

        $students = UserSiswa::whereIn('id', $siswaIds)->get()->keyBy('id');

        $courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)

            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($g) => $g->pluck('mata_kuliah_id'));

        $allCourseIds = $courseIdsBySiswa->flatten()->unique();
        $taskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('mata_kuliah_id')
            ->map(fn ($tasks) => $tasks->count());

        return $krsInCourse->map(function ($krs) use ($students, $courseIdsBySiswa, $taskCountsByCourse) {
            $currentSemester = $students->get($krs->siswa_id)?->semester;
            $courseIds = $currentSemester !== null
                ? Krs::where('siswa_id', $krs->siswa_id)->where('semester', $currentSemester)->pluck('mata_kuliah_id')
                : $courseIdsBySiswa->get($krs->siswa_id, collect());
            $count = $courseIds->sum(fn ($id) => $taskCountsByCourse->get($id, 0));
            $siswa = $krs->siswa ?? $students->get($krs->siswa_id);

            return [
                'siswa_id' => $krs->siswa_id,
                'nim' => $siswa?->nim ?? '-',
                'nama_siswa' => $siswa?->name ?? '-',
                'count' => $count,
                'status' => self::forCount($count),
            ];
        });
    }

    public static function studentWeeklySummary(UserSiswa $student, $weekStart, $weekEnd): array
    {
        $courseIds = Krs::where('siswa_id', $student->id)->pluck('mata_kuliah_id');
        $taskCount = Tugas::whereIn('mata_kuliah_id', $courseIds)
            ->whereBetween('deadline', [
                Carbon::parse($weekStart)->toDateString(),
                Carbon::parse($weekEnd)->toDateString(),
            ])
            ->count();
        $status = self::forCount($taskCount);

        return [
            'siswa_id' => $student->id,
            'nim' => $student->nim,
            'nama' => $student->name,
            'task_count' => $taskCount,
            'status' => $status,
            'label' => self::label($status),
            'color' => self::colorClass($status),
            'risk_score' => self::riskScoreForStudent($student, $weekStart, $weekEnd),
        ];
    }

    public static function riskScoreForStudent(UserSiswa $student, $weekStart, $weekEnd): int
    {
        $semester = $student->semester ?? 1;
        $weekStartStr = Carbon::parse($weekStart)->toDateString();
        $weekEndStr = Carbon::parse($weekEnd)->toDateString();
        $urgentStart = now()->startOfDay()->toDateString();
        $urgentEnd = now()->copy()->addDays(self::URGENT_TASK_DAYS)->endOfDay()->toDateString();

        $data = Krs::where('krs.siswa_id', $student->id)
            ->where('krs.semester', $semester)
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
            ->leftJoin('tugas', function ($join) use ($weekStart, $weekEnd) {
                $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                    ->whereBetween('tugas.deadline', [
                        $weekStart->toDateString(),
                        $weekEnd->toDateString(),
                    ]);
            })
            ->selectRaw('
                COUNT(tugas.id) as weekly_task_count,
                SUM(CASE WHEN tugas.deadline >= ? AND tugas.deadline <= ? THEN 1 ELSE 0 END) as urgent_count
            ', [
                now()->startOfDay()->toDateString(),
                now()->addDays(self::URGENT_TASK_DAYS)->endOfDay()->toDateString(),
            ])
            ->first();

        $weeklyTaskCount = (int) ($data->weekly_task_count ?? 0);
        $urgentTaskCount = (int) ($data->urgent_count ?? 0);

        $latestIpk = $student->ipkHistory()->latest('semester')->first();
        $previousIpk = $student->ipkHistory()
            ->where('semester', '<', $latestIpk?->semester ?? 0)
            ->latest('semester')
            ->first();

        $loadScore = min(45, $weeklyTaskCount * 12);
        $urgencyScore = min(25, $urgentTaskCount * 8);
        $ipkScore = 0;

        if ($latestIpk) {
            if ((float) $latestIpk->ipk < 2.75) {
                $ipkScore += 20;
            } elseif ((float) $latestIpk->ipk < 3.0) {
                $ipkScore += 12;
            }

            if ($previousIpk && ((float) $latestIpk->ipk < (float) $previousIpk->ipk)) {
                $ipkScore += min(10, (int) round((((float) $previousIpk->ipk - (float) $latestIpk->ipk) * 20)));
            }
        }

        return min(100, $loadScore + $urgencyScore + $ipkScore);
    }

    public static function recommendSks(UserSiswa $student, int $riskScore): array
    {
        $latest = $student->ipkHistory()->latest('semester')->first();

        if ($latest?->rekomendasi_sks) {
            $sks = (int) $latest->rekomendasi_sks;
            $reason = 'Menggunakan rekomendasi SKS terakhir dari riwayat IPK.';
        } else {
            $ipk = (float) ($latest?->ipk ?? 3.0);
            $sks = match (true) {
                $riskScore >= 70 || $ipk < 2.75 => 18,
                $riskScore >= 40 || $ipk < 3.0 => 20,
                $ipk >= 3.5 && $riskScore < 35 => 24,
                default => 22,
            };
            $reason = 'Dihitung dari IPK terakhir dan risiko beban tugas minggu ini.';
        }

        return [
            'sks' => $sks,
            'reason' => $reason,
        ];
    }

    public static function competencyByCourse(UserSiswa $student): array
    {
        return NilaiTugas::query()
            ->where('nilai_tugas.siswa_id', $student->id)
            ->join('tugas', 'nilai_tugas.tugas_id', '=', 'tugas.id')
            ->join('mata_kuliah', 'tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->selectRaw('mata_kuliah.nama as nama, AVG(nilai_tugas.nilai) as average_score')
            ->groupBy('mata_kuliah.id', 'mata_kuliah.nama')
            ->orderBy('mata_kuliah.nama')
            ->get()
            ->map(function ($row) {
                $score = round((float) $row->average_score, 1);

                return [
                    'nama' => $row->nama,
                    'score' => $score,
                    'label' => match (true) {
                        $score >= 85 => 'Sangat Baik',
                        $score >= 75 => 'Baik',
                        $score >= 65 => 'Cukup',
                        default => 'Perlu Pendampingan',
                    },
                ];
            })
            ->values()
            ->toArray();
    }

    public static function rescheduleSuggestions(int $mataKuliahId, string $deadline, int $limit = 3, ?int $excludeTaskId = null): array
    {
        $deadlineDate = Carbon::parse($deadline);
        $studentIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        $studentCourseMap = Krs::whereIn('siswa_id', $studentIds)

            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($krsList) => $krsList->pluck('mata_kuliah_id')->toArray())
            ->toArray();

        $suggestions = [];

        for ($i = 1; $i <= 14 && count($suggestions) < $limit; $i++) {
            $candidate = $deadlineDate->copy()->addDays($i);
            $weekStart = $candidate->copy()->startOfWeek();
            $weekEnd = $candidate->copy()->endOfWeek();
            $worstCount = 0;

            foreach ($studentIds as $studentId) {
                $courseIds = $studentCourseMap[$studentId] ?? [];
                if (empty($courseIds)) {
                    continue;
                }

                $count = Tugas::whereIn('mata_kuliah_id', $courseIds)
                    ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->when($excludeTaskId, fn ($q) => $q->where('id', '!=', $excludeTaskId))
                    ->count() + 1;
                $worstCount = max($worstCount, $count);
            }

            $status = self::forCount($worstCount);
            if (in_array($status, [self::LIGHT, self::NORMAL], true)) {
                $suggestions[] = [
                    'value' => $candidate->format('Y-m-d\TH:i'),
                    'label' => $candidate->translatedFormat('d M Y H:i'),
                    'status' => $status,
                    'count' => $worstCount,
                ];
            }
        }

        return $suggestions;
    }

    public static function aggregatePreviewForDosen(UserDosen $dosen): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return MataKuliah::where('dosen_id', $dosen->id)
            ->get()
            ->map(function ($course) use ($weekStart, $weekEnd) {
                $rows = self::weeklyLoadForCourse($course->id, $weekStart, $weekEnd);
                $worst = $rows->pluck('status')
                    ->sortByDesc(fn ($status) => self::severity($status))
                    ->first() ?? self::LIGHT;

                return [
                    'id' => $course->id,
                    'nama' => $course->nama,
                    'kode' => $course->kode,
                    'students' => $rows->count(),
                    'avg_tasks' => round($rows->avg('count') ?? 0, 1),
                    'worst_status' => $worst,
                    'label' => self::label($worst),
                    'color' => self::colorClass($worst),
                ];
            })
            ->values()
            ->toArray();
    }

    public static function paRiskCards(UserDosen $dosen): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return DosenPa::where('dosen_id', $dosen->id)
            ->with('siswa')
            ->get()
            ->map(function ($mapping) use ($weekStart, $weekEnd) {
                if (! $mapping->siswa) {
                    return null;
                }

                // Batch eager load ipkHistory for all students here if needed
                // For now, it's fetched per student inside studentWeeklySummary which is fine.

                return self::studentWeeklySummary($mapping->siswa, $weekStart, $weekEnd);
            })
            ->filter()
            ->sortByDesc('risk_score')
            ->values()
            ->toArray();
    }

    public static function prodiWeeklyTrend(int $weeks = 8): array
    {
        $trend = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            $distribution = self::weeklyLoadDistribution($weekStart, $weekEnd);

            $trend[] = [
                'label' => $weekStart->translatedFormat('d M'),
                'ringan' => $distribution[self::LIGHT],
                'normal' => $distribution[self::NORMAL],
                'berat' => $distribution[self::HEAVY],
                'overload' => $distribution[self::OVERLOAD],
            ];
        }

        return $trend;
    }

    /**
     * Get distribution of students by load category for the week.
     * Returns array like ['ringan' => 10, 'normal' => 5, 'berat' => 3, 'overload' => 2]
     */
    public static function weeklyLoadDistribution($weekStart, $weekEnd): array
    {
        $distribution = [
            self::LIGHT => 0,
            self::NORMAL => 0,
            self::HEAVY => 0,
            self::OVERLOAD => 0,
        ];

        $start = $weekStart instanceof \DateTimeInterface ? $weekStart->format('Y-m-d') : Carbon::parse($weekStart)->toDateString();
        $end = $weekEnd instanceof \DateTimeInterface ? $weekEnd->format('Y-m-d') : Carbon::parse($weekEnd)->toDateString();

        $taskCounts = UserSiswa::select('user_siswa.id')
            ->leftJoin('krs', function ($join) {
                $join->on('krs.siswa_id', '=', 'user_siswa.id')
                    ->on('krs.semester', '=', 'user_siswa.semester');
            })
            ->leftJoin('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
            ->leftJoin('tugas', function ($join) use ($start, $end) {
                $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                    ->whereBetween('tugas.deadline', [$start, $end]);
            })
            ->groupBy('user_siswa.id')
            ->selectRaw('COUNT(tugas.id) as task_count')
            ->pluck('task_count', 'user_siswa.id');

        foreach ($taskCounts as $count) {
            $status = self::forCount((int) $count);
            $distribution[$status]++;
        }

        return $distribution;
    }

    /**
     * Get average tasks per week per course (for Prodi dashboard table)
     */
    public static function averageTasksPerWeekPerCourse(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return Krs::with(['mataKuliah.tugas' => function ($q) use ($weekStart, $weekEnd) {
            $q->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }])
            ->get()
            ->groupBy('mata_kuliah_id')
            ->map(function ($krsGroup) {
                $mk = $krsGroup->first()->mataKuliah;
                if (! $mk) {
                    return null;
                }

                $totalStudents = $krsGroup->count();
                $totalTasks = $krsGroup->sum(fn ($krs) => $krs->mataKuliah?->tugas->count() ?? 0);
                $avg = $totalStudents > 0 ? round($totalTasks / $totalStudents, 1) : 0;
                $status = self::forCount((int) $avg); // approximate

                return [
                    'nama' => $mk->nama,
                    'avg_tasks_week' => $avg,
                    'status' => $status,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
