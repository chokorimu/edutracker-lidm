<?php

namespace App\Console\Commands;

use App\Models\Krs;
use App\Models\Notifikasi;
use App\Models\NotifikasiDosen;
use App\Models\UserSiswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckBebanAkademik extends Command
{
    protected $signature = 'beban:check';

    protected $description = 'Check student workload and send notifications for overloads and deadline collisions';

    public function handle(): int
    {
        $overloadSksThreshold = 24;
        $deadlineCollisionThreshold = 3;
        $collisionWindowDays = 7;
        $chunkSize = 100;

        $collisionStart = now()->toDateString();
        $collisionEnd = now()->copy()->addDays($collisionWindowDays)->endOfDay()->toDateString();

        UserSiswa::with('dosenPa.dosen')->chunk($chunkSize, function ($students) use ($overloadSksThreshold, $deadlineCollisionThreshold, $collisionWindowDays, $collisionStart, $collisionEnd) {
            $studentIds = $students->pluck('id');

            $sksByStudent = Krs::query()
                ->from('krs')
                ->selectRaw('krs.siswa_id as siswa_id, SUM(COALESCE(mata_kuliah.sks, 0)) as total_sks')
                ->join('user_siswa', 'user_siswa.id', '=', 'krs.siswa_id')
                ->leftJoin('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
                ->whereIn('krs.siswa_id', $studentIds)
                ->whereColumn('krs.semester', 'user_siswa.semester')
                ->groupBy('krs.siswa_id')
                ->pluck('total_sks', 'krs.siswa_id');

            $collisionByStudent = Krs::query()
                ->from('krs')
                ->selectRaw('krs.siswa_id as siswa_id, COUNT(tugas.id) as collision_count')
                ->join('user_siswa', 'user_siswa.id', '=', 'krs.siswa_id')
                ->leftJoin('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
                ->leftJoin('tugas', function ($join) use ($collisionStart, $collisionEnd) {
                    $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                        ->whereBetween('tugas.deadline', [$collisionStart, $collisionEnd])
                        ->where('tugas.deadline', '>=', now()->toDateString());
                })
                ->whereIn('krs.siswa_id', $studentIds)
                ->whereColumn('krs.semester', 'user_siswa.semester')
                ->whereNotNull('tugas.id')
                ->groupBy('krs.siswa_id')
                ->pluck('collision_count', 'krs.siswa_id');

            foreach ($students as $siswa) {
                try {
                    $totalSks = (int) ($sksByStudent[$siswa->id] ?? 0);

                    if ($totalSks > $overloadSksThreshold) {
                        Notifikasi::create([
                            'siswa_id' => $siswa->id,
                            'judul' => 'Beban SKS Overload',
                            'pesan' => "Beban SKS kamu semester ini ({$totalSks} SKS) melebihi batas aman.",
                            'tipe' => 'overload_sks',
                            'sumber' => 'system',
                            'is_read' => false,
                        ]);

                        $dosenPa = $siswa->dosenPa->sortByDesc('created_at')->first();
                        if ($dosenPa?->dosen) {
                            NotifikasiDosen::create([
                                'dosen_id' => $dosenPa->dosen_id,
                                'judul' => "SKS Overload: {$siswa->name}",
                                'pesan' => "Mahasiswa {$siswa->name} ({$siswa->nim}) memiliki beban {$totalSks} SKS (overload).",
                                'tipe' => 'peringatan_siswa',
                                'sumber' => 'system',
                                'is_read' => false,
                            ]);
                        }
                    }

                    $collisionCount = (int) ($collisionByStudent[$siswa->id] ?? 0);

                    if ($collisionCount >= $deadlineCollisionThreshold) {
                        Notifikasi::create([
                            'siswa_id' => $siswa->id,
                            'judul' => 'Deadline Padat',
                            'pesan' => "Ada {$collisionCount} tugas dalam {$collisionWindowDays} hari ke depan. Segera rencanakan waktumu!",
                            'tipe' => 'deadline_collision',
                            'sumber' => 'system',
                            'is_read' => false,
                        ]);

                        $dosenPa = $siswa->dosenPa->sortByDesc('created_at')->first();
                        if ($dosenPa?->dosen) {
                            NotifikasiDosen::create([
                                'dosen_id' => $dosenPa->dosen_id,
                                'judul' => "Deadline Padat: {$siswa->name}",
                                'pesan' => "Mahasiswa {$siswa->name} ({$siswa->nim}) memiliki {$collisionCount} deadline dalam {$collisionWindowDays} hari ke depan.",
                                'tipe' => 'deadline_collision',
                                'sumber' => 'system',
                                'is_read' => false,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("CheckBebanAkademik failed for student {$siswa->id}: ".$e->getMessage(), [
                        'exception' => $e,
                        'student_id' => $siswa->id,
                    ]);

                    continue;
                }
            }
        });

        $this->info('Beban akademik check completed.');

        return Command::SUCCESS;
    }
}
