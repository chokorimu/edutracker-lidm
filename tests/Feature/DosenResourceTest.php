<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DosenResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tugas_counts_toward_workload_warning(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.test',
            'password' => 'password',
            'nim' => '220101999',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'BD-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 1',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->toDateString(),
            'deskripsi' => 'Satu',
        ]);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->toDateString(),
            'deskripsi' => 'Dua',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas 3',
                'deskripsi' => 'Tiga',
                'bobot' => 20,
                'deadline' => now()->addDays(2)->toDateString(),
            ])
            ->assertSessionHasErrors('beban_warning')
            ->assertSessionHas('deadline_suggestions');

        $this->assertDatabaseMissing('tugas', ['nama' => 'Tugas 3']);
    }

    public function test_override_saves_one_dosen_notification_for_high_workload(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.test',
            'password' => 'password',
            'nim' => '220101999',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'BD-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 1',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->toDateString(),
            'deskripsi' => 'Satu',
        ]);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->toDateString(),
            'deskripsi' => 'Dua',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas 3',
                'deskripsi' => 'Tiga',
                'bobot' => 20,
                'deadline' => now()->addDays(2)->toDateString(),
                'override' => '1',
            ])
            ->assertRedirect(route('dosen.dashboard', ['tab' => 'tugas']));

        $this->assertDatabaseHas('tugas', [
            'nama' => 'Tugas 3',
            'status_beban' => 'berat',
        ]);
        $this->assertDatabaseCount('notifikasi_dosen', 1);
    }
}
