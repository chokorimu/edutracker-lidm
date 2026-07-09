<?php

namespace Database\Seeders;

use App\Models\DosenPa;
use App\Models\IpkHistory;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\Notifikasi;
use App\Models\Tugas;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSiswaSeeder extends Seeder
{
    public function run(): void
    {
        $siswa = UserSiswa::updateOrCreate(
            ['email' => 'andi@edutrack.test'],
            [
                'name' => 'Andi Mahasiswa',
                'password' => Hash::make('siswa123'),
                'nim' => '220101001',
                'prodi' => 'Teknik Informatika',
                'semester' => 3,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'ahmad.fauzan@student.ac.id'],
            [
                'name' => 'Ahmad Fauzan',
                'password' => Hash::make('password123'),
                'nim' => '202600001',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'bintang.r@student.ac.id'],
            [
                'name' => 'Bintang Ramadhan',
                'password' => Hash::make('password123'),
                'nim' => '202600002',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'cahyani.putri@student.ac.id'],
            [
                'name' => 'Cahyani Putri',
                'password' => Hash::make('password123'),
                'nim' => '202600003',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'dimas.saputra@student.ac.id'],
            [
                'name' => 'Dimas Saputra',
                'password' => Hash::make('password123'),
                'nim' => '202600004',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'eko.prasetyo@student.ac.id'],
            [
                'name' => 'Eko Prasetyo',
                'password' => Hash::make('password123'),
                'nim' => '202600005',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        UserSiswa::updateOrCreate(
            ['email' => 'fajar.n@student.ac.id'],
            [
                'name' => 'Fajar Nugroho',
                'password' => Hash::make('password123'),
                'nim' => '202600006',
                'prodi' => 'Informatika',
                'semester' => 1,
            ]
        );

        $dosen = UserDosen::updateOrCreate(
            ['email' => 'dosen@edutrack.test'],
            [
                'name' => 'Dr. Rahmat Hidayat, S.Kom., M.T.',
                'password' => Hash::make('dosen123'),
                'nidn' => 'NIDN-001',
                'fakultas' => 'Ilmu Komputer',
            ]
        );

        DosenPa::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tahun_ajaran' => '2026/2027'],
            ['dosen_id' => $dosen->id]
        );

        $pemrogramanWeb = MataKuliah::updateOrCreate(
            ['kode' => 'PW01'],
            [
                'nama' => 'Pemrograman Web',
                'sks' => 3,
                'dosen_id' => $dosen->id,
                'tahun_ajaran' => '2026/2027',
                'semester' => 3,
            ]
        );

        $basisData = MataKuliah::updateOrCreate(
            ['kode' => 'BDL01'],
            [
                'nama' => 'Basis Data Lanjut',
                'sks' => 3,
                'dosen_id' => $dosen->id,
                'tahun_ajaran' => '2026/2027',
                'semester' => 3,
            ]
        );

        foreach ([$pemrogramanWeb, $basisData] as $mataKuliah) {
            Krs::updateOrCreate(
                ['siswa_id' => $siswa->id, 'mata_kuliah_id' => $mataKuliah->id],
                [
                    'semester' => 3,
                    'tahun_ajaran' => '2026/2027',
                    'status' => 'aktif',
                ]
            );
        }

        IpkHistory::updateOrCreate(
            ['siswa_id' => $siswa->id, 'semester' => 2],
            [
                'tahun_ajaran' => '2025/2026',
                'ipk' => 3.25,
                'total_sks' => 40,
                'rekomendasi_sks' => 21,
            ]
        );

        $analisisNormalisasi = Tugas::updateOrCreate(
            ['mata_kuliah_id' => $basisData->id, 'nama' => 'Analisis Normalisasi'],
            [
                'bobot' => 20,
                'deadline' => now()->addDays(2)->toDateString(),
                'deskripsi' => 'Tugas DB',
            ]
        );

        Tugas::updateOrCreate(
            ['mata_kuliah_id' => $pemrogramanWeb->id, 'nama' => 'Implementasi CRUD Laravel'],
            [
                'bobot' => 25,
                'deadline' => now()->addDays(5)->toDateString(),
                'deskripsi' => 'Bangun CRUD sederhana dengan Laravel.',
            ]
        );

        NilaiTugas::updateOrCreate(
            ['tugas_id' => $analisisNormalisasi->id, 'siswa_id' => $siswa->id],
            ['nilai' => 88]
        );

        Notifikasi::updateOrCreate(
            ['siswa_id' => $siswa->id, 'judul' => 'Info Baru'],
            [
                'pesan' => 'Pesan dari DB',
                'tipe' => 'info',
                'sumber' => 'Seeder',
                'is_read' => false,
            ]
        );
    }
}
