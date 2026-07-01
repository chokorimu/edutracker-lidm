<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use App\Services\BebanCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DosenResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kelas_tab_renders_course_list_and_detail(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Kelas',
            'email' => 'dosen-kelas@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-KELAS',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Kelas',
            'email' => 'siswa-kelas@example.test',
            'password' => 'password',
            'nim' => '220101996',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Interaksi Manusia Komputer',
            'kode' => 'IMK-001',
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
            'nama' => 'Wireframe',
            'bobot' => 100,
            'deadline' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Buat wireframe',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'kelas']))
            ->assertOk()
            ->assertSee('Mata Kuliah Anda')
            ->assertSee('Interaksi Manusia Komputer');

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]))
            ->assertOk()
            ->assertSee('Tambah Tugas')
            ->assertSee('Daftar Tugas & Nilai', false)
            ->assertSee('Wireframe')
            ->assertSee('Siswa Kelas');
    }

    public function test_kelas_task_preview_uses_krs_semester_and_nearest_task_week(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Preview Kelas',
            'email' => 'dosen-preview-kelas@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-PREVIEW-KELAS',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Preview Kelas',
            'email' => 'siswa-preview-kelas@example.test',
            'password' => 'password',
            'nim' => '220101995',
            'prodi' => 'Informatika',
            'semester' => 2,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Dasar Basis Data',
            'kode' => 'IF104',
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

        $deadline = now()->addWeek()->startOfWeek()->addDay()->setTime(10, 0);

        foreach (['Normalisasi', 'ERD'] as $taskName) {
            Tugas::create([
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => $taskName,
                'bobot' => 50,
                'deadline' => $deadline->format('Y-m-d H:i:s'),
                'deskripsi' => $taskName,
            ]);
        }

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]))
            ->assertOk()
            ->assertSee('Dasar Basis Data (IF104)')
            ->assertSee('1 mahasiswa · rata-rata 2 tugas')
            ->assertSee('Status terberat: Normal')
            ->assertSee($deadline->copy()->startOfWeek()->translatedFormat('d M'), false);
    }

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
            ->assertRedirect(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]));

        $this->assertDatabaseHas('tugas', [
            'nama' => 'Tugas 3',
            'status_beban' => 'berat',
        ]);
        $this->assertDatabaseCount('notifikasi_dosen', 1);
        $this->assertDatabaseHas('notifikasi', [
            'siswa_id' => $siswa->id,
            'judul' => 'Beban Akademik Berat',
            'tipe' => 'peringatan',
            'sumber' => 'system',
            'is_read' => false,
        ]);
    }

    public function test_update_tugas_excludes_current_task_before_recounting_workload(): void
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
        $tugas = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->toDateString(),
            'deskripsi' => 'Dua',
            'status' => 'aktif',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->put(route('dosen.tugas.update', $tugas->id), [
                'nama' => 'Tugas 2 Revisi',
                'deskripsi' => 'Dua revisi',
                'bobot' => 25,
                'deadline' => now()->addDays(2)->toDateString(),
                'status' => 'aktif',
            ])
            ->assertRedirect(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]));

        $this->assertDatabaseHas('tugas', [
            'id' => $tugas->id,
            'nama' => 'Tugas 2 Revisi',
            'status_beban' => 'normal',
        ]);
    }

    public function test_preview_beban_returns_projected_workload_and_suggestions(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen-preview@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Preview',
            'email' => 'siswa-preview@example.test',
            'password' => 'password',
            'nim' => '220101998',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Rekayasa Perangkat Lunak',
            'kode' => 'RPL-001',
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

        $deadline = now()->addDays(4)->setTime(10, 0);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 1',
            'bobot' => 20,
            'deadline' => $deadline->copy()->subDay()->toDateString(),
            'deskripsi' => 'Satu',
        ]);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => $deadline->copy()->subDay()->toDateString(),
            'deskripsi' => 'Dua',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->postJson(route('dosen.tugas.preview-beban'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'deadline' => $deadline->format('Y-m-d\TH:i'),
            ])
            ->assertOk()
            ->assertJsonPath('course.id', $mataKuliah->id)
            ->assertJsonPath('summary.students', 1)
            ->assertJsonPath('summary.worst_status', BebanCalculator::HEAVY)
            ->assertJsonPath('summary.label', 'Berat')
            ->assertJsonPath('summary.color', BebanCalculator::colorClass(BebanCalculator::HEAVY))
            ->assertJsonPath('summary.needs_warning', true)
            ->assertJsonPath('students.0.current_count', 2)
            ->assertJsonPath('students.0.projected_count', 3)
            ->assertJsonPath('students.0.status', BebanCalculator::HEAVY)
            ->assertJsonCount(3, 'suggestions');
    }

    public function test_dosen_can_store_nilai_and_update_krs_final_grade(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Nilai',
            'email' => 'dosen-nilai@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-003',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Nilai',
            'email' => 'siswa-nilai@example.test',
            'password' => 'password',
            'nim' => '220101997',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Pemrograman Web',
            'kode' => 'PW-001',
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
        $tugasA = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas A',
            'bobot' => 50,
            'deadline' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'deskripsi' => 'A',
        ]);
        $tugasB = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas B',
            'bobot' => 50,
            'deadline' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'deskripsi' => 'B',
        ]);
        NilaiTugas::create([
            'tugas_id' => $tugasA->id,
            'siswa_id' => $siswa->id,
            'nilai' => 80,
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.nilai.store', [$tugasB->id, $siswa->id]), [
                'nilai' => 90,
                'komentar' => 'Bagus',
            ])
            ->assertRedirect(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]))
            ->assertSessionHas('status', 'Nilai disimpan.');

        $this->assertDatabaseHas('nilai_tugas', [
            'tugas_id' => $tugasB->id,
            'siswa_id' => $siswa->id,
            'nilai' => 90,
            'komentar' => 'Bagus',
        ]);
        $this->assertDatabaseHas('krs', [
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'nilai_akhir' => 85,
            'nilai_huruf' => 'A',
        ]);
    }

    public function test_preview_beban_forbids_other_dosen_course(): void
    {
        $owner = UserDosen::create([
            'name' => 'Dosen Owner',
            'email' => 'owner-preview@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);
        $other = UserDosen::create([
            'name' => 'Dosen Other',
            'email' => 'other-preview@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-002',
            'fakultas' => 'Teknik',
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'BD-001',
            'sks' => 3,
            'dosen_id' => $owner->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);

        $this->actingAs($other, 'dosen')
            ->postJson(route('dosen.tugas.preview-beban'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'deadline' => now()->addDays(3)->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();
    }

    public function test_dosen_can_view_and_download_student_submission(): void
    {
        Storage::fake('local');

        $dosen = UserDosen::create([
            'name' => 'Dosen Submission',
            'email' => 'dosen-submission@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-SUBMISSION',
            'fakultas' => 'Teknik',
        ]);
        $otherDosen = UserDosen::create([
            'name' => 'Dosen Lain',
            'email' => 'dosen-lain@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-LAIN',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Submission',
            'email' => 'siswa-submission@example.test',
            'password' => 'password',
            'nim' => '220101995',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Sistem Terdistribusi',
            'kode' => 'STD-001',
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
        $tugas = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Paper Konsensus',
            'bobot' => 100,
            'deadline' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'deskripsi' => 'PDF paper',
        ]);
        Storage::disk('local')->put('submissions/paper.pdf', 'pdf-content');
        $submission = TugasSubmission::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'file_path' => 'submissions/paper.pdf',
            'file_name' => 'paper-konsensus.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]))
            ->assertOk()
            ->assertSee('File Submission')
            ->assertSee('paper-konsensus.pdf')
            ->assertSee('Tepat waktu');

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.submission.download', $submission->id))
            ->assertOk()
            ->assertDownload('paper-konsensus.pdf');

        $this->actingAs($otherDosen, 'dosen')
            ->get(route('dosen.submission.download', $submission->id))
            ->assertForbidden();
    }
}
