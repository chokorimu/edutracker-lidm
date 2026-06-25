<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dashboard_uses_database_queries(): void
    {
        // 1. Setup Student (Semester 1)
        $siswa = UserSiswa::create([
            'name' => 'Siswa Sem 1',
            'email' => 'siswa1@test.test',
            'password' => bcrypt('siswa123'),
            'nim' => '220101001',
            'prodi' => 'Informatika',
            'semester' => 1,
        ]);

        // 2. Setup Dosen
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@test.test',
            'password' => bcrypt('dosen123'),
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);

        // 3. Setup Mata Kuliah
        $mk = MataKuliah::create([
            'nama' => 'Pemrograman Web',
            'kode' => 'PW01',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'semester' => 1,
            'tahun_ajaran' => '2025/2026',
        ]);

        // 4. Register KRS
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 1,
            'status' => 'aktif',
            'tahun_ajaran' => '2025/2026',
        ]);

        // 5. Add Notification
        Notifikasi::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Info Baru',
            'pesan' => 'Pesan dari DB',
            'tipe' => 'info',
            'is_read' => false,
        ]);

        // 6. Verify data exists in database
        $this->assertDatabaseHas('user_siswa', [
            'email' => 'siswa1@test.test',
            'name' => 'Siswa Sem 1',
            'semester' => 1,
        ]);

        $this->assertDatabaseHas('krs', [
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 1,
        ]);

        $this->assertDatabaseHas('notifikasi', [
            'siswa_id' => $siswa->id,
            'pesan' => 'Pesan dari DB',
        ]);

        // 7. Test monitoring tab renders 200, confirms DB-backed data presence
        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard', ['tab' => 'monitoring']));

        $response->assertStatus(200);

        // Verify the page shows the semester info (SKS = 3) and the row exists
        $response->assertSee('3 SKS');
    }
}
