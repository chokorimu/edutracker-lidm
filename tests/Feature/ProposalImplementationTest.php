<?php

namespace Tests\Feature;

use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\UserDosen;
use App\Models\UserProdi;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProposalImplementationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_warning_notifies_dosen_pa_for_deadline_collision(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen PA',
            'email' => 'pa@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-PA',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Padat',
            'email' => 'padat@example.test',
            'password' => 'password',
            'nim' => '220101333',
            'prodi' => 'Informatika',
            'semester' => 5,
        ]);
        $mk = MataKuliah::create([
            'nama' => 'Sistem Terdistribusi',
            'kode' => 'ST-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 5,
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 5,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);
        DosenPa::create([
            'dosen_id' => $dosen->id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran' => '2026/2027',
        ]);

        foreach ([1, 2, 3] as $index) {
            Tugas::create([
                'mata_kuliah_id' => $mk->id,
                'nama' => "Tugas Padat {$index}",
                'bobot' => 10,
                'deadline' => now()->addDays($index)->toDateString(),
                'deskripsi' => 'Deadline padat',
            ]);
        }

        Artisan::call('beban:check');

        $this->assertDatabaseHas('notifikasi_dosen', [
            'dosen_id' => $dosen->id,
            'tipe' => 'deadline_collision',
        ]);
    }

    public function test_prodi_dashboard_renders_weekly_trend_chart(): void
    {
        $prodi = UserProdi::create([
            'name' => 'Kaprodi Test',
            'email' => 'kaprodi@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($prodi, 'prodi')
            ->get(route('prodi.dashboard'))
            ->assertOk()
            ->assertSee('Tren Beban Keseluruhan 8 Minggu')
            ->assertSee('weeklyTrendChart');
    }
}
