<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\NotifikasiDosen;
use App\Models\UserSiswa;
use Illuminate\Console\Command;

class CheckBebanAkademik extends Command
{
    protected $signature = 'beban:check';

    protected $description = 'Check student workload and send notifications for overloads and deadline collisions';

    public function handle(): int
    {
        $overloadSksThreshold = 24;
        $deadlineCollisionThreshold = 3;
        $collisionWindowDays = 7;

        $allSiswa = UserSiswa::with(['krs.mataKuliah.tugas', 'dosenPa.dosen'])->get();

        foreach ($allSiswa as $siswa) {
            $semester = $siswa->semester;

            // 1. Check SKS overload
            $totalSks = $siswa->krs
                ->where('semester', $semester)
                ->sum(fn ($krs) => $krs->mataKuliah?->sks ?? 0);

            if ($totalSks > $overloadSksThreshold) {
                Notifikasi::create([
                    'siswa_id' => $siswa->id,
                    'judul' => 'Beban SKS Overload',
                    'pesan' => "Beban SKS kamu semester ini ({$totalSks} SKS) melebihi batas aman.",
                    'tipe' => 'overload_sks',
                    'sumber' => 'system',
                    'is_read' => false,
                ]);

                $dosenPa = $siswa->dosenPa;
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

            // 2. Check deadline collision
            $collisionCount = $siswa->krs
                ->flatMap(fn ($krs) => $krs->mataKuliah?->tugas ?? collect())
                ->filter(fn ($tugas) => $tugas->deadline >= now() && $tugas->deadline <= now()->addDays($collisionWindowDays))
                ->count();

            if ($collisionCount >= $deadlineCollisionThreshold) {
                Notifikasi::create([
                    'siswa_id' => $siswa->id,
                    'judul' => 'Deadline Padat',
                    'pesan' => "Ada {$collisionCount} tugas dalam {$collisionWindowDays} hari ke depan. Segera rencanakan waktumu!",
                    'tipe' => 'deadline_collision',
                    'sumber' => 'system',
                    'is_read' => false,
                ]);
            }
        }

        $this->info('Beban akademik check completed.');

        return Command::SUCCESS;
    }
}
