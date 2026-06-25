<?php

namespace App\Services;

use App\Models\Krs;
use App\Models\Tugas;
use App\Models\UserSiswa;
use Illuminate\Support\Collection;

class BebanCalculator
{
    public const LIGHT = 'ringan';

    public const NORMAL = 'normal';

    public const HEAVY = 'berat';

    public const OVERLOAD = 'overload';

    public static function forCount(int $count): string
    {
        return match (true) {
            $count <= 1 => self::LIGHT,
            $count === 2 => self::NORMAL,
            $count === 3 => self::HEAVY,
            default => self::OVERLOAD,
        };
    }

    /**
     * Get weekly task count for a single student.
     */
    public static function weeklyLoadForStudent(int $siswaId, $weekStart, $weekEnd): array
    {
        $count = Tugas::whereHas('mataKuliah.krs', function ($q) use ($siswaId) {
            $q->where('siswa_id', $siswaId);
        })
            ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();

        return [
            'count' => $count,
            'status' => self::forCount($count),
        ];
    }

    /**
     * Get distribution of students by load category for the week.
     * Returns array like ['ringan' => 10, 'normal' => 5, 'berat' => 3, 'overload' => 2]
     */
    public static function weeklyLoadDistribution($weekStart, $weekEnd): array
    {
        $students = UserSiswa::with(['krs.mataKuliah.tugas' => function ($q) use ($weekStart, $weekEnd) {
            $q->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }])->get();

        $distribution = [
            self::LIGHT => 0,
            self::NORMAL => 0,
            self::HEAVY => 0,
            self::OVERLOAD => 0,
        ];

        foreach ($students as $student) {
            $taskCount = $student->krs
                ->flatMap(fn ($krs) => $krs->mataKuliah?->tugas ?? collect())
                ->count();

            $status = self::forCount($taskCount);
            $distribution[$status]++;
        }

        return $distribution;
    }

    /**
     * Get weekly load per student for a specific course.
     * Returns collection of [siswa_id, nama_siswa, count, status]
     */
    public static function weeklyLoadForCourse(int $mataKuliahId, $weekStart, $weekEnd): Collection
    {
        return Krs::where('mata_kuliah_id', $mataKuliahId)
            ->with(['siswa.tugas' => function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }])
            ->get()
            ->map(function ($krs) {
                $tugasCount = $krs->siswa->tugas->count();

                return [
                    'siswa_id' => $krs->siswa_id,
                    'nama_siswa' => $krs->siswa->name,
                    'count' => $tugasCount,
                    'status' => self::forCount($tugasCount),
                ];
            });
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
                $status = self::forCount($avg > 3 ? 4 : (int) $avg); // approximate

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
