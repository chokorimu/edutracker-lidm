<?php

namespace App\Jobs;

use App\Models\Krs;
use App\Models\Notifikasi;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SendBebanNaikNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly int $mataKuliahId,
        private readonly int $tugasId,
        private readonly string $tugasNama,
        private readonly string $deadline,
    ) {}

    public function handle(): void
    {
        $tugas = Tugas::find($this->tugasId);

        // Task may have been deleted before the job ran
        if (! $tugas) {
            return;
        }

        $deadline = Carbon::parse($this->deadline);
        $weekStart = $deadline->copy()->startOfWeek();
        $weekEnd = $deadline->copy()->endOfWeek();

        $siswaIds = Krs::where('mata_kuliah_id', $this->mataKuliahId)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return;
        }

        $courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)
            ->get(['siswa_id', 'mata_kuliah_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('mata_kuliah_id'));

        $allCourseIds = $courseIdsBySiswa->flatten()->unique()->values();

        $weeklyTugas = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart, $weekEnd])
            ->where('id', '!=', $this->tugasId)
            ->get(['id', 'mata_kuliah_id'])
            ->groupBy('mata_kuliah_id');

        $weeklyTugasIds = $weeklyTugas->flatten()->pluck('id');
        $submittedPairs = TugasSubmission::whereIn('tugas_id', $weeklyTugasIds)
            ->whereIn('siswa_id', $siswaIds)
            ->get(['tugas_id', 'siswa_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('tugas_id')->toArray());

        $severity = [
            BebanCalculator::LIGHT => 0,
            BebanCalculator::NORMAL => 1,
            BebanCalculator::HEAVY => 2,
            BebanCalculator::OVERLOAD => 3,
        ];

        $notificationsToInsert = [];
        $now = now();

        foreach ($siswaIds as $siswaId) {
            $studentCourseIds = $courseIdsBySiswa->get($siswaId, collect());
            $submittedTugasIds = $submittedPairs->get($siswaId, []);

            $countBefore = $studentCourseIds->sum(function ($courseId) use ($weeklyTugas, $submittedTugasIds) {
                return $weeklyTugas->get($courseId, collect())->pluck('id')->diff($submittedTugasIds)->count();
            });

            $countAfter = $countBefore + 1;
            $statusBefore = BebanCalculator::forCount($countBefore);
            $statusAfter = BebanCalculator::forCount($countAfter);

            if ($severity[$statusAfter] > $severity[$statusBefore]) {
                $label = BebanCalculator::label($statusAfter);
                $notificationsToInsert[] = [
                    'siswa_id' => $siswaId,
                    'judul' => "Beban Minggu Ini Naik: {$label}",
                    'pesan' => "Tugas baru '{$this->tugasNama}' membuat beban tugasmu minggu ini naik ke level {$label}.",
                    'tipe' => 'peringatan',
                    'sumber' => 'system',
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($notificationsToInsert)) {
            // Chunk to avoid exceeding MySQL max_allowed_packet
            foreach (array_chunk($notificationsToInsert, 500) as $chunk) {
                Notifikasi::insert($chunk);
            }

            // Bust siswa dashboard cache for affected students
            $affectedSiswaIds = array_unique(array_column($notificationsToInsert, 'siswa_id'));
            foreach ($affectedSiswaIds as $siswaId) {
                Cache::forget("siswa_dashboard_{$siswaId}");
            }
        }
    }
}
