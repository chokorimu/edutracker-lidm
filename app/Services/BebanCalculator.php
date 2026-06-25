<?php

namespace App\Services;

use App\Models\Krs;
use App\Models\Tugas;
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
}
