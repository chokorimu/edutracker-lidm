<?php

namespace App\Console\Commands;

use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\NotifikasiDosen;
use App\Models\Tugas;
use App\Models\UserSiswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBebanAkademik extends Command
{
    protected $signature = 'beban:check';
    protected $description = 'Check student workload and send notifications for overloads and deadline collisions';

    public function handle(): int
    {
        $overloadSksThreshold = 24;
        $deadlineCollisionThreshold = 3;
        $collisionWindowDays = 7;

        foreach (UserSiswa::all() as $siswa) {
            $semester = $siswa->semester;

            // 1. Check SKS overload
            $totalSks = DB::table('krs')
                ->join('mata_kuliah', 'krs.mata_kuliah_id', '=', 'mata_kuliah.id')
                ->where('krs.siswa_id', $siswa->id)
                ->where('krs.semester', $semester)
                ->sum('mata_kuliah.sks');

            if ($totalSks > $overloadSksThreshold) {
                // Notify student
                Notifikasi::create([
                    'siswa_id' => $siswa->id,
                    'judul' => 'Beban SKS Overload',
                    'pesan' => "Beban SKS kamu semester ini ({$totalSks} SKS) melebihi batas aman.",
                    'tipe' => 'overload_sks',
                    'sumber' => 'system',
                    'is_read' => false,
                ]);

                // Notify dosen PA
                $dosenPa = DosenPa::where('siswa_id', $siswa->id)->first();
                if ($dosenPa) {
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

            // 2. Check deadline collision (≥3 tasks in next 7 days)
            $collisionCount = Tugas::whereHas('mataKuliah.krs', function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
                ->whereBetween('deadline', [now(), now()->addDays($collisionWindowDays)])
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
