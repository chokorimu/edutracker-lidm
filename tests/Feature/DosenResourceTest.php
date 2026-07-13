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
use Illuminate\Support\Facades\Cache;
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
            ->assertSee('Tetap lanjut dengan override')
            ->assertSee('Daftar Tugas & Nilai', false)
            ->assertSee('Wireframe')
            ->assertSee('Siswa Kelas');
    }

    public function test_kelas_task_preview_uses_krs_semester_and_current_week(): void
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
        $submittedSiswa = UserSiswa::create([
            'name' => 'Siswa Preview Selesai',
            'email' => 'siswa-preview-selesai@example.test',
            'password' => 'password',
            'nim' => '220101994',
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
        Krs::create([
            'siswa_id' => $submittedSiswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        $deadline = now()->addDays(8)->setTime(10, 0);

        $tasks = collect();
        foreach (['Normalisasi', 'ERD'] as $taskName) {
            $tasks->push(Tugas::create([
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => $taskName,
                'bobot' => 50,
                'deadline' => $deadline->format('Y-m-d H:i:s'),
                'deskripsi' => $taskName,
            ]));
        }
        $tasks->each(fn (Tugas $task) => TugasSubmission::create([
            'tugas_id' => $task->id,
            'siswa_id' => $submittedSiswa->id,
            'file_path' => 'submissions/preview-kelas.pdf',
            'file_name' => 'preview-kelas.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]));

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]))
            ->assertOk()
            ->assertSee('Dasar Basis Data (IF104)')
            ->assertSee('2 mahasiswa · rata-rata 0 tugas')
            ->assertSee('Status terberat: Ringan')
            ->assertSee(now()->startOfDay()->translatedFormat('d M'), false);
    }

    public function test_beban_tab_filters_workload_table_by_selected_course(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Beban',
            'email' => 'dosen-beban-filter@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-BEBAN',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Beban',
            'email' => 'siswa-beban-filter@example.test',
            'password' => 'password',
            'nim' => '220101994',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $firstCourse = MataKuliah::create([
            'nama' => 'Basis Data',
            'kode' => 'BD-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);
        $secondCourse = MataKuliah::create([
            'nama' => 'Pemrograman Web',
            'kode' => 'PW-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);

        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $firstCourse->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $secondCourse->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'beban', 'mk_beban' => $secondCourse->id]));

        $response->assertOk()
            ->assertSee('Pilih Mata Kuliah')
            ->assertSee('Pemrograman Web (PW-001)');

        $data = $response->original->getData()['data'];

        $this->assertSame((string) $secondCourse->id, $data['selectedBebanMkId']);
        $this->assertCount(1, $data['workloadData']);
        $this->assertSame($secondCourse->id, $data['workloadData']->first()['id']);
    }

    public function test_beban_tab_excludes_submitted_tasks_per_student(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Beban Personal',
            'email' => 'dosen-beban-personal@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-BEBAN-PERSONAL',
            'fakultas' => 'Teknik',
        ]);
        $submittedSiswa = UserSiswa::create([
            'name' => 'Siswa Sudah Selesai',
            'email' => 'siswa-selesai@example.test',
            'password' => 'password',
            'nim' => '220102001',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $pendingSiswa = UserSiswa::create([
            'name' => 'Siswa Belum Submit',
            'email' => 'siswa-belum-submit@example.test',
            'password' => 'password',
            'nim' => '220102002',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Sistem Terdistribusi',
            'kode' => 'ST-001',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 4,
        ]);

        foreach ([$submittedSiswa, $pendingSiswa] as $siswa) {
            Krs::create([
                'siswa_id' => $siswa->id,
                'mata_kuliah_id' => $mataKuliah->id,
                'semester' => 4,
                'tahun_ajaran' => '2026/2027',
                'status' => 'aktif',
            ]);
        }

        $tasks = collect(['Kuis', 'Laporan', 'Presentasi'])->map(fn (string $name) => Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => $name,
            'bobot' => 20,
            'deadline' => now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => $name,
        ]));

        $tasks->each(fn (Tugas $task) => TugasSubmission::create([
            'tugas_id' => $task->id,
            'siswa_id' => $submittedSiswa->id,
            'file_path' => 'submissions/done.pdf',
            'file_name' => 'done.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]));

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard', ['tab' => 'beban', 'mk_beban' => $mataKuliah->id]));

        $response->assertOk();

        $rows = $response->original->getData()['data']['workloadData']->first()['thisWeek']->keyBy('siswa_id');

        $this->assertSame(0, $rows->get($submittedSiswa->id)['count']);
        $this->assertSame(BebanCalculator::LIGHT, $rows->get($submittedSiswa->id)['status']);
        $this->assertSame(3, $rows->get($pendingSiswa->id)['count']);
        $this->assertSame(BebanCalculator::HEAVY, $rows->get($pendingSiswa->id)['status']);

        $summary = BebanCalculator::studentWeeklySummary($submittedSiswa, now()->startOfDay(), now()->addDays(6)->endOfDay());

        $this->assertSame(0, $summary['task_count']);
        $this->assertSame(BebanCalculator::LIGHT, $summary['status']);
        $this->assertSame(0, $summary['risk_score']);
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
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Satu',
        ]);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Dua',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas 3',
                'deskripsi' => 'Tiga',
                'bobot' => 20,
                'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('beban_warning')
            ->assertSessionHas('deadline_suggestions');

        $this->assertDatabaseMissing('tugas', ['nama' => 'Tugas 3']);
    }

    public function test_new_tugas_warning_excludes_existing_submitted_tasks(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Submitted Warning',
            'email' => 'dosen-submitted-warning@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-SUBMITTED-WARNING',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Submitted Warning',
            'email' => 'siswa-submitted-warning@example.test',
            'password' => 'password',
            'nim' => '220102003',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Keamanan Informasi',
            'kode' => 'KI-001',
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

        $tasks = collect(['Tugas 1', 'Tugas 2'])->map(fn (string $name) => Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => $name,
            'bobot' => 20,
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => $name,
        ]));

        $tasks->each(fn (Tugas $task) => TugasSubmission::create([
            'tugas_id' => $task->id,
            'siswa_id' => $siswa->id,
            'file_path' => 'submissions/submitted.pdf',
            'file_name' => 'submitted.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]));

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas 3',
                'deskripsi' => 'Tiga',
                'bobot' => 20,
                'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            ])
            ->assertSessionDoesntHaveErrors('beban_warning')
            ->assertRedirect(route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliah->id]));

        $this->assertDatabaseHas('tugas', [
            'nama' => 'Tugas 3',
            'status_beban' => BebanCalculator::LIGHT,
        ]);
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
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Satu',
        ]);
        Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Dua',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas 3',
                'deskripsi' => 'Tiga',
                'bobot' => 20,
                'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
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
            'judul' => 'Beban Minggu Ini Naik: Berat',
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
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Satu',
        ]);
        $tugas = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'deskripsi' => 'Dua',
            'status' => 'aktif',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->put(route('dosen.tugas.update', $tugas->id), [
                'nama' => 'Tugas 2 Revisi',
                'deskripsi' => 'Dua revisi',
                'bobot' => 25,
                'deadline' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
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
        $submittedSiswa = UserSiswa::create([
            'name' => 'Siswa Sudah Submit',
            'email' => 'siswa-submitted-preview@example.test',
            'password' => 'password',
            'nim' => '220101997',
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
        Krs::create([
            'siswa_id' => $submittedSiswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'semester' => 4,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        $deadline = now()->addDays(8)->setTime(10, 0);
        $firstTask = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 1',
            'bobot' => 20,
            'deadline' => $deadline->copy()->addDay()->format('Y-m-d H:i:s'),
            'deskripsi' => 'Satu',
        ]);
        $secondTask = Tugas::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 2',
            'bobot' => 20,
            'deadline' => $deadline->copy()->addDay()->format('Y-m-d H:i:s'),
            'deskripsi' => 'Dua',
        ]);
        foreach ([$firstTask, $secondTask] as $task) {
            TugasSubmission::create([
                'tugas_id' => $task->id,
                'siswa_id' => $submittedSiswa->id,
                'file_path' => 'submissions/preview.pdf',
                'file_name' => 'preview.pdf',
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

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

    public function test_dosen_preview_cache_is_invalidated_on_tugas_create_and_delete(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Cache',
            'email' => 'dosen-cache@example.test',
            'password' => 'password',
            'nidn' => 'NIDN-CACHE',
            'fakultas' => 'Teknik',
        ]);
        $siswa = UserSiswa::create([
            'name' => 'Siswa Cache',
            'email' => 'siswa-cache@example.test',
            'password' => 'password',
            'nim' => 'NIM-CACHE',
            'prodi' => 'Informatika',
            'semester' => 3,
        ]);
        $mk = MataKuliah::create([
            'nama' => 'Cache Test',
            'kode' => 'CT-01',
            'sks' => 3,
            'dosen_id' => $dosen->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 3,
        ]);
        Krs::create([
            'siswa_id' => $siswa->id,
            'mata_kuliah_id' => $mk->id,
            'semester' => 3,
            'tahun_ajaran' => '2026/2027',
            'status' => 'aktif',
        ]);

        // Seed cache
        Cache::put("dosen_preview_{$dosen->id}", ['cached_data'], 3600);
        $this->assertNotNull(Cache::get("dosen_preview_{$dosen->id}"));

        // Create tugas -> cache should be invalidated
        $deadline = now()->addDays(3)->format('Y-m-d H:i:s');
        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mk->id,
                'nama' => 'Tugas Cache Test',
                'deadline' => $deadline,
            ])
            ->assertRedirect();

        $this->assertNull(Cache::get("dosen_preview_{$dosen->id}"));

        // Re-seed cache and delete tugas
        Cache::put("dosen_preview_{$dosen->id}", ['cached_data'], 3600);
        $tugas = Tugas::where('nama', 'Tugas Cache Test')->first();

        $this->actingAs($dosen, 'dosen')
            ->delete(route('dosen.tugas.destroy', $tugas->id))
            ->assertRedirect();

        $this->assertNull(Cache::get("dosen_preview_{$dosen->id}"));
    }

    public function test_dosen_can_set_custom_task_weight_and_auto_rebalance_the_rest(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Bobot', 'email' => 'dosen-bobot@example.test',
            'password' => 'password', 'nidn' => 'NIDN-BOBOT', 'fakultas' => 'Teknik',
        ]);
        $mataKuliah = MataKuliah::create([
            'nama' => 'Testing Bobot', 'kode' => 'TB-001', 'sks' => 3,
            'dosen_id' => $dosen->id, 'tahun_ajaran' => '2026/2027', 'semester' => 4,
        ]);

        // Create first task with no bobot (auto) via store
        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas Auto 1',
                'deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        // First task should be 100% (only auto task)
        $this->assertDatabaseHas('tugas', [
            'nama' => 'Tugas Auto 1', 'is_bobot_locked' => false,
        ]);
        $auto1 = Tugas::where('nama', 'Tugas Auto 1')->first();
        $this->assertEquals(100, $auto1->bobot);

        // Create second task with custom bobot 20%
        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas Locked',
                'bobot' => 20,
                'deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        $this->assertDatabaseHas('tugas', [
            'nama' => 'Tugas Locked', 'bobot' => 20, 'is_bobot_locked' => true,
        ]);

        // Auto task should be rebalanced to 80% (100 - 20 locked)
        $auto1->refresh();
        $this->assertEquals(80, $auto1->bobot);

        // Create third task (also auto)
        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.tugas.store'), [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama' => 'Tugas Auto 2',
                'deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        // Now: Locked=20%, Auto1 and Auto2 share 80% = 40% each
        $auto1->refresh();
        $auto2 = Tugas::where('nama', 'Tugas Auto 2')->first();
        $this->assertEquals(40, $auto1->bobot);
        $this->assertEquals(40, $auto2->bobot);

        // Verify locked task unchanged
        $locked = Tugas::where('nama', 'Tugas Locked')->first();
        $this->assertEquals(20, $locked->bobot);
        $this->assertTrue((bool) $locked->is_bobot_locked);
    }
}
