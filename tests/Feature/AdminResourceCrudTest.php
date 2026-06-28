<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_resource_crud(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Data Master')
            ->assertSee('Siswa');
    }

    public function test_admin_can_create_update_and_delete_every_resource(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen Base',
            'email' => 'dosen-base@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-BASE',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Base',
            'email' => 'siswa-base@example.test',
            'password' => 'password',
            'nim' => 'NIM-BASE',
            'prodi' => 'Informatika',
            'semester' => 3,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'BD-BASE',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 3,
        ]);
        $tugas = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas Base',
            'bobot' => 20,
            'deadline' => '2026-07-01',
            'deskripsi' => 'Dasar',
            'status_beban' => 'aktif',
            'override' => false,
        ]);

        $this->actingAs($admin, 'admin');

        $cases = [
            'admins' => [
                'table' => 'user_admin',
                'create' => ['name' => 'Admin Dua', 'email' => 'admin-dua@example.test', 'password' => 'password'],
                'update' => ['name' => 'Admin Dua Update', 'email' => 'admin-dua-update@example.test', 'password' => ''],
                'match' => ['email' => 'admin-dua-update@example.test'],
            ],
            'dosens' => [
                'table' => 'user_dosens',
                'create' => ['name' => 'Dosen Dua', 'email' => 'dosen-dua@example.test', 'password' => 'password', 'nidn' => 'NIDN-002', 'fakultas' => 'Teknik'],
                'update' => ['name' => 'Dosen Dua Update', 'email' => 'dosen-dua-update@example.test', 'password' => '', 'nidn' => 'NIDN-002U', 'fakultas' => 'Sains'],
                'match' => ['email' => 'dosen-dua-update@example.test'],
            ],
            'siswas' => [
                'table' => 'user_siswa',
                'create' => ['name' => 'Siswa Dua', 'email' => 'siswa-dua@example.test', 'password' => 'password', 'nim' => 'NIM-002', 'prodi' => 'Informatika', 'semester' => 4],
                'update' => ['name' => 'Siswa Dua Update', 'email' => 'siswa-dua-update@example.test', 'password' => '', 'nim' => 'NIM-002U', 'prodi' => 'Sistem Informasi', 'semester' => 5],
                'match' => ['email' => 'siswa-dua-update@example.test'],
            ],
            'mata-kuliah' => [
                'table' => 'mata_kuliah',
                'create' => ['nama' => 'Algoritma', 'kode' => 'ALG-001', 'sks' => 4, 'dosen_id' => $dosen->id, 'tahun_ajaran' => '2026/2027', 'semester' => 2],
                'update' => ['nama' => 'Algoritma Update', 'kode' => 'ALG-001U', 'sks' => 3, 'dosen_id' => $dosen->id, 'tahun_ajaran' => '2027/2028', 'semester' => 3],
                'match' => ['kode' => 'ALG-001U'],
            ],
            'dosen-pa' => [
                'table' => 'dosen_pa',
                'create' => ['dosen_id' => $dosen->id, 'siswa_id' => $siswa->id, 'tahun_ajaran' => '2026/2027'],
                'update' => ['dosen_id' => $dosen->id, 'siswa_id' => $siswa->id, 'tahun_ajaran' => '2027/2028'],
                'match' => ['tahun_ajaran' => '2027/2028'],
            ],
            'ipk-history' => [
                'table' => 'ipk_history',
                'create' => ['siswa_id' => $siswa->id, 'ipk' => 3.2, 'semester' => 3, 'tahun_ajaran' => '2026/2027', 'total_sks' => 60, 'rekomendasi_sks' => 21],
                'update' => ['siswa_id' => $siswa->id, 'ipk' => 3.6, 'semester' => 4, 'tahun_ajaran' => '2027/2028', 'total_sks' => 80, 'rekomendasi_sks' => 24],
                'match' => ['ipk' => 3.6],
            ],
            'kalender-akademik' => [
                'table' => 'kalender_akademik',
                'create' => ['judul' => 'UTS', 'tanggal' => '2026-08-01', 'tipe' => 'ujian', 'tahun_ajaran' => '2026/2027', 'created_by' => $admin->id],
                'update' => ['judul' => 'UAS', 'tanggal' => '2026-08-15', 'tipe' => 'ujian', 'tahun_ajaran' => '2026/2027', 'created_by' => $admin->id],
                'match' => ['judul' => 'UAS'],
            ],
            'krs' => [
                'table' => 'krs',
                'create' => ['siswa_id' => $siswa->id, 'mata_kuliah_id' => $mataKuliah->id, 'semester' => 3, 'tahun_ajaran' => '2026/2027', 'nilai_akhir' => 80, 'nilai_huruf' => 'A', 'status' => 'aktif'],
                'update' => ['siswa_id' => $siswa->id, 'mata_kuliah_id' => $mataKuliah->id, 'semester' => 3, 'tahun_ajaran' => '2026/2027', 'nilai_akhir' => 85, 'nilai_huruf' => 'A', 'status' => 'selesai'],
                'match' => ['status' => 'selesai'],
            ],
            'notifikasi' => [
                'table' => 'notifikasi',
                'create' => ['siswa_id' => $siswa->id, 'judul' => 'Info', 'pesan' => 'Pesan siswa', 'tipe' => 'info', 'sumber' => 'admin', 'is_read' => '0'],
                'update' => ['siswa_id' => $siswa->id, 'judul' => 'Info Update', 'pesan' => 'Pesan update', 'tipe' => 'info', 'sumber' => 'admin', 'is_read' => '1'],
                'match' => ['judul' => 'Info Update'],
            ],
            'notifikasi-dosen' => [
                'table' => 'notifikasi_dosen',
                'create' => ['dosen_id' => $dosen->id, 'mata_kuliah_id' => $mataKuliah->id, 'tugas_id' => $tugas->id, 'judul' => 'Info Dosen', 'pesan' => 'Pesan dosen', 'tipe' => 'info', 'sumber' => 'admin', 'is_read' => '0'],
                'update' => ['dosen_id' => $dosen->id, 'mata_kuliah_id' => $mataKuliah->id, 'tugas_id' => $tugas->id, 'judul' => 'Info Dosen Update', 'pesan' => 'Pesan update', 'tipe' => 'info', 'sumber' => 'admin', 'is_read' => '1'],
                'match' => ['judul' => 'Info Dosen Update'],
            ],
            'laporan' => [
                'table' => 'laporan',
                'create' => ['judul' => 'Laporan Awal', 'tipe' => 'akademik', 'periode' => '2026', 'file_path' => 'laporan/awal.pdf', 'created_by' => $admin->id],
                'update' => ['judul' => 'Laporan Update', 'tipe' => 'akademik', 'periode' => '2027', 'file_path' => 'laporan/update.pdf', 'created_by' => $admin->id],
                'match' => ['judul' => 'Laporan Update'],
            ],
            'pengaturan' => [
                'table' => 'pengaturan',
                'create' => ['setting_key' => 'maks_sks', 'value' => '24', 'updated_by' => $admin->id],
                'update' => ['setting_key' => 'maks_sks_update', 'value' => '21', 'updated_by' => $admin->id],
                'match' => ['setting_key' => 'maks_sks_update'],
            ],
        ];

        foreach ($cases as $resource => $case) {
            $this->post(route('admin.resources.store', $resource), $case['create'])
                ->assertRedirect(route('admin.dashboard', ['resource' => $resource]));

            $id = (int) \DB::table($case['table'])->latest('id')->value('id');

            if (in_array($resource, ['admins', 'dosens', 'siswas'], true)) {
                $storedPassword = \DB::table($case['table'])->where('id', $id)->value('password');

                $this->assertTrue(Hash::check($case['create']['password'], $storedPassword));
                $this->assertNotSame($case['create']['password'], $storedPassword);
            }

            $this->put(route('admin.resources.update', [$resource, $id]), $case['update'])
                ->assertRedirect(route('admin.dashboard', ['resource' => $resource]));

            $this->assertDatabaseHas($case['table'], ['id' => $id, ...$case['match']]);

            $this->delete(route('admin.resources.destroy', [$resource, $id]))
                ->assertRedirect(route('admin.dashboard', ['resource' => $resource]));

            $this->assertDatabaseMissing($case['table'], ['id' => $id]);
        }
    }

    public function test_admin_can_add_krs_as_package_for_a_student(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin-package@example.test',
            'password' => 'password',
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen Paket',
            'email' => 'dosen-package@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-PACKAGE',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Paket',
            'email' => 'siswa-package@example.test',
            'password' => 'password',
            'nim' => 'NIM-PACKAGE',
            'prodi' => 'Informatika',
            'semester' => 3,
        ]);
        $courses = collect(['Algoritma', 'Basis Data', 'Jaringan'])->map(fn (string $name, int $index) => MataKuliah::create([
            'nama' => $name,
            'kode' => 'PKT-00'.($index + 1),
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 3,
        ]));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard', ['resource' => 'krs']))
            ->assertOk()
            ->assertSee('Tambah KRS Paket')
            ->assertSee('Semester 3 - 2026/2027 (3 mata kuliah)');

        $payload = [
            '_mode' => 'batch',
            'siswa_id' => $siswa->id,
            'krs_package' => '2026/2027|3',
            'status' => 'aktif',
        ];

        $this->post(route('admin.resources.store', 'krs'), $payload)
            ->assertRedirect(route('admin.dashboard', ['resource' => 'krs']))
            ->assertSessionHas('status', 'KRS paket berhasil diproses: 3 dibuat, 0 sudah ada.');

        foreach ($courses as $course) {
            $this->assertDatabaseHas('krs', [
                'siswa_id' => $siswa->id,
                'mata_kuliah_id' => $course->id,
                'semester' => 3,
                'tahun_ajaran' => '2026/2027',
                'status' => 'aktif',
            ]);
        }

        $this->post(route('admin.resources.store', 'krs'), $payload)
            ->assertRedirect(route('admin.dashboard', ['resource' => 'krs']))
            ->assertSessionHas('status', 'KRS paket berhasil diproses: 0 dibuat, 3 sudah ada.');

        $this->assertSame(3, Krs::where('siswa_id', $siswa->id)->count());
    }

    public function test_admin_krs_list_is_grouped_by_student_with_total_sks(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin-krs-list@example.test',
            'password' => 'password',
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen KRS List',
            'email' => 'dosen-krs-list@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-KRS-LIST',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa KRS List',
            'email' => 'siswa-krs-list@example.test',
            'password' => 'password',
            'nim' => 'NIM-KRS-LIST',
            'prodi' => 'Informatika',
            'semester' => 3,
        ]);
        $basisData = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'KRS-BD',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 3,
        ]);
        $algoritma = MataKuliah::create([
            'nama' => 'Algoritma',
            'kode' => 'KRS-ALG',
            'sks' => 4,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 3,
        ]);

        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $basisData->id,
            'semester' => 3,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $algoritma->id,
            'semester' => 3,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard', ['resource' => 'krs']))
            ->assertOk()
            ->assertSee('Total 1 murid dengan 2 data KRS.')
            ->assertSee('Siswa KRS List')
            ->assertSee('2 mata kuliah')
            ->assertSee('7 SKS')
            ->assertSee('Basis Data')
            ->assertSee('Algoritma');
    }
}
