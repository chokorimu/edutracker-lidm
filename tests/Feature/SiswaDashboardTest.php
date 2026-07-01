<?php

namespace Tests\Feature;

use App\Models\IpkHistory;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\Notifikasi;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'profile_completed' => true,
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
            'profile_completed' => true,
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

    public function test_siswa_analytics_and_calendar_are_data_driven(): void
    {
        $siswa = UserSiswa::create([
            'name' => 'Siswa Analytics',
            'email' => 'analytics@test.test',
            'password' => bcrypt('siswa123'),
            'nim' => '220101002',
            'prodi' => 'Informatika',
            'semester' => 3,
            'profile_completed' => true,
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen Analytics',
            'email' => 'dosen-analytics@test.test',
            'password' => bcrypt('dosen123'),
            'nidn' => 'NIDN-002',
            'fakultas' => 'Teknik',
        ]);
        $mk = MataKuliah::create([
            'nama' => 'Basis Data Lanjut',
            'kode' => 'BDL01',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'semester' => 3,
            'tahun_ajaran' => '2026/2027',
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 3,
            'status' => 'aktif',
            'tahun_ajaran' => '2026/2027',
        ]);
        IpkHistory::create([
            'siswa_id' => $siswa->id,
            'semester' => 2,
            'tahun_ajaran' => '2025/2026',
            'ipk' => 3.25,
            'total_sks' => 40,
            'rekomendasi_sks' => 21,
        ]);
        $task = Tugas::create([
            'mata_kuliah_id' => $mk->id,
            'nama' => 'Analisis Normalisasi',
            'bobot' => 20,
            'deadline' => now()->day(15)->toDateString(),
            'deskripsi' => 'Tugas DB',
        ]);
        NilaiTugas::create([
            'tugas_id' => $task->id,
            'siswa_id' => $siswa->id,
            'nilai' => 88,
        ]);

        $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard', ['tab' => 'analytics']))
            ->assertOk()
            ->assertSee('Tren Historis IPK')
            ->assertSee('data-ipk-chart', false)
            ->assertSee('Grafik Tren Historis IPK')
            ->assertSee('Sem 2')
            ->assertSee('3.25')
            ->assertSee('21 SKS')
            ->assertSee('Basis Data Lanjut')
            ->assertSee('Sangat Baik');

        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard', ['tab' => 'calendar', 'month' => now()->month, 'year' => now()->year, 'day' => 15]));

        $response
            ->assertOk()
            ->assertSee('Timeline Deadline')
            ->assertSee('Analisis Normalisasi');

        $data = $response->original->getData()['data'];

        $this->assertIsArray($data['monthly_tasks']);
        $this->assertIsArray($data['monthly_tasks'][15] ?? null);
    }

    public function test_siswa_dashboard_uses_current_week_for_workload(): void
    {
        $siswa = UserSiswa::create([
            'name' => 'Siswa Beban',
            'email' => 'beban@test.test',
            'password' => bcrypt('siswa123'),
            'nim' => '220101003',
            'prodi' => 'Informatika',
            'semester' => 1,
            'profile_completed' => true,
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen Beban',
            'email' => 'dosen-beban@test.test',
            'password' => bcrypt('dosen123'),
            'nidn' => 'NIDN-003',
            'fakultas' => 'Teknik',
        ]);
        $mk = MataKuliah::create([
            'nama' => 'Algoritma',
            'kode' => 'ALG01',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'semester' => 1,
            'tahun_ajaran' => '2026/2027',
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 1,
            'status' => 'aktif',
            'tahun_ajaran' => '2026/2027',
        ]);

        $currentWeek = now()->startOfWeek();
        Tugas::create([
            'mata_kuliah_id' => $mk->id,
            'nama' => 'Tugas minggu berjalan',
            'bobot' => 25,
            'deadline' => $currentWeek->copy()->addDay()->setTime(10, 0),
            'deskripsi' => 'Tugas beban',
        ]);

        $deadlineWeek = now()->addWeek()->startOfWeek();
        foreach ([0, 1, 2] as $index) {
            Tugas::create([
                'mata_kuliah_id' => $mk->id,
                'nama' => 'Tugas '.$index,
                'bobot' => 33.33,
                'deadline' => $deadlineWeek->copy()->addDays($index)->setTime(10, 0),
                'deskripsi' => 'Tugas beban',
            ]);
        }

        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard'));

        $response->assertOk();

        $data = $response->original->getData()['data'];

        $this->assertSame(1, $data['weekly_task_count']);
        $this->assertSame(
            $currentWeek->translatedFormat('d M').' - '.$currentWeek->copy()->endOfWeek()->translatedFormat('d M Y'),
            $data['workload_week_label']
        );
    }

    public function test_profile_uses_ipk_history_for_active_semester_completed_sks_and_cumulative_ipk(): void
    {
        $siswa = UserSiswa::create([
            'name' => 'Siswa Profil',
            'email' => 'siswa-profil@example.test',
            'password' => bcrypt('siswa123'),
            'nim' => '220101005',
            'prodi' => 'Informatika',
            'semester' => 2,
            'profile_completed' => true,
        ]);
        IpkHistory::create([
            'siswa_id' => $siswa->id,
            'semester' => 1,
            'tahun_ajaran' => '2025/2026',
            'ipk' => 3.80,
            'total_sks' => 20,
            'rekomendasi_sks' => 21,
        ]);
        IpkHistory::create([
            'siswa_id' => $siswa->id,
            'semester' => 2,
            'tahun_ajaran' => '2025/2026',
            'ipk' => 3.70,
            'total_sks' => 20,
            'rekomendasi_sks' => 24,
        ]);
        IpkHistory::create([
            'siswa_id' => $siswa->id,
            'semester' => 3,
            'tahun_ajaran' => '2025/2026',
            'ipk' => 3.30,
            'total_sks' => 24,
            'rekomendasi_sks' => null,
        ]);

        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard', ['tab' => 'profile']));

        $response->assertOk()
            ->assertSee('3.60')
            ->assertSee('64')
            ->assertSee('Semester 4');

        $profile = $response->original->getData()['data']['profile'];

        $this->assertSame('3.60', $profile['ipk']);
        $this->assertSame(64, $profile['sks_lulus']);
        $this->assertSame(0, $profile['sks_semester']);
        $this->assertSame(4, $profile['semester']);
    }

    public function test_siswa_can_submit_pdf_and_view_submission_status(): void
    {
        Storage::fake('local');

        $siswa = UserSiswa::create([
            'name' => 'Siswa Submit',
            'email' => 'siswa-submit@example.test',
            'password' => bcrypt('siswa123'),
            'nim' => '220101004',
            'prodi' => 'Informatika',
            'semester' => 2,
            'profile_completed' => true,
        ]);
        $dosen = UserDosen::create([
            'name' => 'Dosen Submit',
            'email' => 'dosen-submit@example.test',
            'password' => bcrypt('dosen123'),
            'nidn' => 'NIDN-004',
            'fakultas' => 'Teknik',
        ]);
        $mk = MataKuliah::create([
            'nama' => 'Pemrograman Mobile',
            'kode' => 'PMB01',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'semester' => 2,
            'tahun_ajaran' => '2026/2027',
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 2,
            'status' => 'aktif',
            'tahun_ajaran' => '2026/2027',
        ]);
        $tugas = Tugas::create([
            'mata_kuliah_id' => $mk->id,
            'nama' => 'Laporan Praktikum',
            'bobot' => 100,
            'deadline' => now()->endOfWeek()->subHour()->format('Y-m-d H:i:s'),
            'deskripsi' => 'Upload PDF',
        ]);

        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard'));

        $this->assertSame(1, $response->original->getData()['data']['weekly_task_count']);

        $this->actingAs($siswa, 'siswa')
            ->post(route('siswa.tugas.submit', $tugas->id), [
                'file' => UploadedFile::fake()
                    ->createWithContent('jawaban.pdf', "%PDF-1.7\n1 0 obj\n<<>>\nendobj\n%%EOF\n")
                    ->mimeType('application/octet-stream'),
            ])
            ->assertRedirect(route('siswa.dashboard', ['tab' => 'tugas', 'mk' => $mk->id]))
            ->assertSessionHas('status', 'Tugas berhasil disubmit.');

        $submission = TugasSubmission::firstOrFail();

        $this->assertSame($tugas->id, $submission->tugas_id);
        $this->assertSame($siswa->id, $submission->siswa_id);
        $this->assertSame('jawaban.pdf', $submission->file_name);
        $this->assertSame('submitted', $submission->status);
        Storage::disk('local')->assertExists($submission->file_path);

        $response = $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard'));

        $this->assertSame(0, $response->original->getData()['data']['weekly_task_count']);

        $this->actingAs($siswa, 'siswa')
            ->get(route('siswa.dashboard', ['tab' => 'tugas', 'mk' => $mk->id]))
            ->assertOk()
            ->assertSee('Laporan Praktikum')
            ->assertSee('Selesai')
            ->assertSee('jawaban.pdf')
            ->assertSee('Download')
            ->assertSee('Ganti PDF');
    }
}
