<?php

namespace App\Services;

use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $weekStart = Carbon::parse($weekStart)->startOfDay();
        $weekEnd = Carbon::parse($weekEnd)->endOfDay();
        $krsInCourse = Krs::where('mata_kuliah_id', $mataKuliahId)->with('siswa')->get();
        $siswaIds = $krsInCourse->pluck('siswa_id');

        $students = UserSiswa::whereIn('id', $siswaIds)->get()->keyBy('id');

        $krsBySiswa = Krs::whereIn('siswa_id', $siswaIds)
            ->get()
            ->groupBy('siswa_id');

        $allCourseIds = $krsBySiswa
            ->flatten(1)
            ->pluck('mata_kuliah_id')
            ->unique()
            ->values();

        $weeklyTugas = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart, $weekEnd])
            ->get(['id', 'mata_kuliah_id'])
            ->groupBy('mata_kuliah_id');

        $weeklyTugasIds = $weeklyTugas->flatten()->pluck('id');
        $submittedPairs = TugasSubmission::whereIn('tugas_id', $weeklyTugasIds)
            ->whereIn('siswa_id', $siswaIds)
            ->get(['tugas_id', 'siswa_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('tugas_id')->toArray());

        return $krsInCourse->map(function ($krs) use ($students, $krsBySiswa, $weeklyTugas, $submittedPairs) {
            $courseIds = $krsBySiswa
                ->get($krs->siswa_id, collect())
                ->where('semester', $krs->semester)
                ->pluck('mata_kuliah_id');
            $submittedTugasIds = $submittedPairs->get($krs->siswa_id, []);
            $count = $courseIds->sum(function ($courseId) use ($weeklyTugas, $submittedTugasIds) {
                return $weeklyTugas->get($courseId, collect())->pluck('id')->diff($submittedTugasIds)->count();
            });
            $siswa = $krs->siswa ?? $students->get($krs->siswa_id);

            return [
                'siswa_id' => $krs->siswa_id,
                'nim' => $siswa?->nim ?? '-',
                'nama_siswa' => $siswa?->name ?? '-',
                'semester' => $krs->semester,
                'count' => $count,
                'status' => self::forCount($count),
            ];
        });
    }

    public static function studentWeeklySummary(UserSiswa $student, $weekStart, $weekEnd): array
    {
        $courseIds = Krs::where('siswa_id', $student->id)->pluck('mata_kuliah_id');

        $weeklyTugasIds = Tugas::whereIn('mata_kuliah_id', $courseIds)
            ->whereBetween('deadline', [
                Carbon::parse($weekStart)->toDateString(),
                Carbon::parse($weekEnd)->toDateString(),
            ])
            ->pluck('id');

        $submittedIds = TugasSubmission::where('siswa_id', $student->id)
            ->whereIn('tugas_id', $weeklyTugasIds)
            ->pluck('tugas_id');

        $taskCount = $weeklyTugasIds->diff($submittedIds)->count();
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
            ->leftJoin('tugas_submission', function ($join) use ($student) {
                $join->on('tugas_submission.tugas_id', '=', 'tugas.id')
                    ->where('tugas_submission.siswa_id', $student->id);
            })
            ->selectRaw('
                COUNT(CASE WHEN tugas.id IS NOT NULL AND tugas_submission.id IS NULL THEN 1 END) as weekly_task_count,
                SUM(CASE WHEN tugas.id IS NOT NULL AND tugas_submission.id IS NULL AND tugas.deadline >= ? AND tugas.deadline <= ? THEN 1 ELSE 0 END) as urgent_count
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

        $allCourseIds = collect($studentCourseMap)->flatten()->unique()->values();

        $windowStart = $deadlineDate->copy()->addDay()->startOfWeek();
        $windowEnd = $deadlineDate->copy()->addDays(14)->endOfWeek();

        $allTugasInWindow = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->when($excludeTaskId, fn ($q) => $q->where('id', '!=', $excludeTaskId))
            ->get(['id', 'mata_kuliah_id', 'deadline'])
            ->groupBy(fn ($t) => Carbon::parse($t->deadline)->startOfWeek()->toDateString());

        $suggestions = [];

        for ($i = 1; $i <= 14 && count($suggestions) < $limit; $i++) {
            $candidate = $deadlineDate->copy()->addDays($i);
            $weekKey = $candidate->copy()->startOfWeek()->toDateString();
            $weekTugas = $allTugasInWindow->get($weekKey, collect());
            $tugasByCourse = $weekTugas->groupBy('mata_kuliah_id');
            $worstCount = 0;

            foreach ($studentIds as $studentId) {
                $courseIds = $studentCourseMap[$studentId] ?? [];
                if (empty($courseIds)) {
                    continue;
                }

                $count = collect($courseIds)->sum(fn ($cid) => $tugasByCourse->get($cid, collect())->count()) + 1;
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
        [$weekStart, $weekEnd] = self::resolveDosenPreviewWeek($dosen);

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
                    'week_label' => $weekStart->translatedFormat('d M').' - '.$weekEnd->translatedFormat('d M Y'),
                ];
            })
            ->values()
            ->toArray();
    }

    private static function resolveDosenPreviewWeek(UserDosen $dosen): array
    {
        $now = now();

        return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
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
        // Compute the full 8-week window once
        $windowStart = now()->subWeeks($weeks - 1)->startOfWeek();
        $windowEnd = now()->endOfWeek();

        // 1 query: get task count per student per week across the entire window
        $rows = UserSiswa::select('user_siswa.id')
            ->leftJoin('krs', function ($join) {
                $join->on('krs.siswa_id', '=', 'user_siswa.id')
                    ->on('krs.semester', '=', 'user_siswa.semester');
            })
            ->leftJoin('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
            ->leftJoin('tugas', function ($join) use ($windowStart, $windowEnd) {
                $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                    ->whereBetween('tugas.deadline', [
                        $windowStart->toDateString(),
                        $windowEnd->toDateString(),
                    ]);
            })
            ->addSelect(DB::raw('tugas.deadline as task_deadline'))
            ->get();

        // Group by student+week in PHP
        $studentWeekCounts = [];
        foreach ($rows as $row) {
            if (! $row->task_deadline) {
                // Ensure students with 0 tasks still appear
                $studentWeekCounts[$row->id] ??= [];

                continue;
            }
            $weekKey = Carbon::parse($row->task_deadline)->startOfWeek()->toDateString();
            $studentWeekCounts[$row->id][$weekKey] = ($studentWeekCounts[$row->id][$weekKey] ?? 0) + 1;
        }

        $trend = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekKey = $weekStart->toDateString();

            $distribution = [
                self::LIGHT => 0,
                self::NORMAL => 0,
                self::HEAVY => 0,
                self::OVERLOAD => 0,
            ];

            foreach ($studentWeekCounts as $weekCounts) {
                $count = $weekCounts[$weekKey] ?? 0;
                $status = self::forCount($count);
                $distribution[$status]++;
            }

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
     * Get average tasks per week per course (for Prodi dashboard table).
     * Uses a single aggregate query instead of loading all KRS into memory.
     */
    public static function averageTasksPerWeekPerCourse(): array
    {
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        // 1 query: count tasks per course this week + count enrolled students
        $results = MataKuliah::select('mata_kuliah.id', 'mata_kuliah.nama')
            ->selectRaw('COUNT(DISTINCT krs.siswa_id) as student_count')
            ->selectRaw('COUNT(DISTINCT tugas.id) as task_count')
            ->leftJoin('krs', 'krs.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->leftJoin('tugas', function ($join) use ($weekStart, $weekEnd) {
                $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                    ->whereBetween('tugas.deadline', [$weekStart, $weekEnd]);
            })
            ->groupBy('mata_kuliah.id', 'mata_kuliah.nama')
            ->havingRaw('COUNT(DISTINCT krs.siswa_id) > 0')
            ->get();

        return $results->map(function ($row) {
            $avg = round((float) $row->task_count, 1);
            $status = self::forCount((int) $avg);

            return [
                'nama' => $row->nama,
                'avg_tasks_week' => $avg,
                'status' => $status,
            ];
        })->values()->toArray();
    }
}
