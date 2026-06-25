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

    public static function weeklyLoadForCourse(int $mataKuliahId, $weekStart, $weekEnd): Collection
    {
        $krsInCourse = Krs::where('mata_kuliah_id', $mataKuliahId)->with('siswa')->get();
        $siswaIds = $krsInCourse->pluck('siswa_id');

        $courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)->get()->groupBy('siswa_id')->map(fn ($g) => $g->pluck('mata_kuliah_id'));

        $allCourseIds = $courseIdsBySiswa->flatten()->unique();
        $taskCountsByCourse = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('mata_kuliah_id')
            ->map(fn ($tasks) => $tasks->count());

        return $krsInCourse->map(function ($krs) use ($courseIdsBySiswa, $taskCountsByCourse) {
            $courseIds = $courseIdsBySiswa->get($krs->siswa_id, collect());
            $count = $courseIds->sum(fn ($id) => $taskCountsByCourse->get($id, 0));

            return [
                'siswa_id' => $krs->siswa_id,
                'nama_siswa' => $krs->siswa->name,
                'count' => $count,
                'status' => self::forCount($count),
            ];
        });
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
