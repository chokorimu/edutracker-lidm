# Skenario Suntik Data Manual (Direct Database Injection)

Dokumen ini memuat rancangan data (Dosen, Siswa, Mata Kuliah, KRS, IPK, dan Tugas) yang akan disuntikkan secara langsung ke database menggunakan skrip PHP Tinker, tanpa menggunakan Seeder. 

Tujuannya agar Anda bisa melihat datanya, mengecek apakah distribusinya sudah pas, dan tinggal mengeksekusinya dalam satu perintah.

---

## 1. Rincian Data yang Akan Disuntikkan

### A. Dosen (5 Orang) & Dosen PA
Dosen akan di-generate sebanyak 5 orang dari berbagai kepakaran. Setiap dosen akan ditugaskan menjadi **Dosen PA untuk 3 Siswa** (Total 15 Siswa = 5 Dosen x 3).

### B. Siswa (15 Orang) & IPK History
Siswa dibagi rata menjadi 3 kelompok semester:
- **Semester 1 (5 Siswa):** Belum punya IPK History. Akan mendapat paket matkul Sem 1 (20 SKS).
- **Semester 2 (5 Siswa):** Punya IPK History untuk Sem 1 (IPK Random 2.8 - 4.0). Akan mendapat paket matkul Sem 2 (20 SKS).
- **Semester 3 (5 Siswa):** Punya IPK History untuk Sem 1 & Sem 2 (IPK Random). Akan mendapat paket matkul Sem 3 (24 SKS).

### C. Mata Kuliah (Paket Semester)
Berikut adalah daftar mata kuliah yang disiapkan (total 22 Matkul), lengkap dengan jadwal (Hari & Jam):

**Semester 1 (7 Matkul | 20 SKS)**
1. Agama (3 SKS) - Senin, 08:00-10:30
2. Kewarganegaraan (2 SKS) - Senin, 13:00-14:40
3. Kalkulus Dasar (3 SKS) - Selasa, 08:00-10:30
4. Algoritma & Pemrograman (3 SKS) - Rabu, 08:00-10:30
5. Pengantar Teknologi Informasi (3 SKS) - Kamis, 08:00-10:30
6. Bahasa Inggris (3 SKS) - Jumat, 08:00-10:30
7. Sistem Digital (3 SKS) - Jumat, 13:00-15:30

**Semester 2 (7 Matkul | 20 SKS)**
1. Struktur Data (3 SKS) - Senin, 08:00-10:30
2. Aljabar Linear (3 SKS) - Selasa, 08:00-10:30
3. Arsitektur Komputer (3 SKS) - Selasa, 13:00-15:30
4. Pemrograman Berorientasi Objek (3 SKS) - Rabu, 08:00-10:30
5. Statistika (3 SKS) - Kamis, 08:00-10:30
6. Bahasa Indonesia (2 SKS) - Kamis, 13:00-14:40
7. Basis Data (3 SKS) - Jumat, 08:00-10:30

**Semester 3 (8 Matkul | 24 SKS)**
1. Pemrograman Web (3 SKS) - Senin, 08:00-10:30
2. Basis Data Lanjut (3 SKS) - Senin, 13:00-15:30
3. Rekayasa Perangkat Lunak (3 SKS) - Selasa, 08:00-10:30
4. Jaringan Komputer (3 SKS) - Rabu, 08:00-10:30
5. Sistem Operasi (3 SKS) - Rabu, 13:00-15:30
6. Kecerdasan Buatan (3 SKS) - Kamis, 08:00-10:30
7. Interaksi Manusia Komputer (3 SKS) - Kamis, 13:00-15:30
8. Etika Profesi (3 SKS) - Jumat, 08:00-10:30

### D. Distribusi Tugas (Tidak Rata)
Setiap mata kuliah akan dibuatkan tepat **2 Tugas**:
- **Tugas 1 (Minggu Ini):** Deadline diacak dari Senin s/d Minggu di minggu ini, jam diacak dari 08:00 s/d 20:00.
- **Tugas 2 (Minggu Depan):** Deadline diacak dari Senin s/d Minggu di minggu *berikutnya*, jam juga diacak.
Kondisi ini menjamin akan ada tabrakan tugas di hari tertentu secara acak (simulasi beban nyata).

---

## 2. Script Eksekusi (Copy-Paste ke Tinker)

Jika Anda sudah ACC rincian di atas, Anda cukup membuka terminal, jalankan `php artisan tinker`, lalu **Copy-Paste** seluruh kode di bawah ini. Kode ini akan otomatis mengeksekusi semua data ke Database Anda.

*(Pastikan Anda sudah menjalankan Migration untuk menambahkan kolom Hari & Jam di tabel `mata_kuliah` sebelum mengeksekusi script ini).*

```php
use App\Models\UserDosen;
use App\Models\UserSiswa;
use App\Models\MataKuliah;
use App\Models\Krs;
use App\Models\IpkHistory;
use App\Models\Tugas;
use App\Models\DosenPa;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

$passwordDosen = Hash::make('dosen123');
$passwordSiswa = Hash::make('siswa123');
$tahunAjaran = '2026/2027';

// 1. BUAT 5 DOSEN
$dosens = [];
for($i=1; $i<=5; $i++) {
    $dosens[] = UserDosen::create([
        'name' => "Dosen Kepakaran $i",
        'email' => "dosen$i@kampus.ac.id",
        'password' => $passwordDosen,
        'nidn' => "10000000$i",
        'fakultas' => 'Fakultas Ilmu Komputer'
    ]);
}

// 2. BUAT 15 SISWA (Sem 1, 2, 3)
$siswas = [];
for($i=1; $i<=15; $i++) {
    $semester = ($i <= 5) ? 1 : (($i <= 10) ? 2 : 3);
    $siswas[] = UserSiswa::create([
        'name' => "Siswa Simulasi $i",
        'email' => "siswa$i@student.ac.id",
        'password' => $passwordSiswa,
        'nim' => "24000$i",
        'prodi' => 'Teknik Informatika',
        'semester' => $semester,
        'profile_completed' => true
    ]);
}

// 3. SET DOSEN PA (1 Dosen pegang 3 Siswa berurutan)
foreach($siswas as $index => $siswa) {
    $dosenIndex = floor($index / 3);
    DosenPa::create([
        'dosen_id' => $dosens[$dosenIndex]->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran' => $tahunAjaran
    ]);
}

// 4. GENERATE IPK HISTORY UNTUK SEMESTER 2 & 3
foreach($siswas as $siswa) {
    if ($siswa->semester >= 2) { // Buat IPK Sem 1
        IpkHistory::create([
            'siswa_id' => $siswa->id, 'semester' => 1, 'tahun_ajaran' => '2025/2026', 
            'ipk' => rand(28, 40) / 10, 'total_sks' => 20, 'rekomendasi_sks' => 24
        ]);
    }
    if ($siswa->semester >= 3) { // Buat IPK Sem 2
        IpkHistory::create([
            'siswa_id' => $siswa->id, 'semester' => 2, 'tahun_ajaran' => '2025/2026', 
            'ipk' => rand(28, 40) / 10, 'total_sks' => 40, 'rekomendasi_sks' => 24
        ]);
    }
}

// 5. BUAT MATA KULIAH
$matkulData = [
    // Semester 1 (7 Matkul)
    ['Agama', 'MK101', 3, 1, 'Senin', '08:00', '10:30'],
    ['Kewarganegaraan', 'MK102', 2, 1, 'Senin', '13:00', '14:40'],
    ['Kalkulus Dasar', 'MK103', 3, 1, 'Selasa', '08:00', '10:30'],
    ['Algoritma & Pemrograman', 'MK104', 3, 1, 'Rabu', '08:00', '10:30'],
    ['Pengantar TI', 'MK105', 3, 1, 'Kamis', '08:00', '10:30'],
    ['Bahasa Inggris', 'MK106', 3, 1, 'Jumat', '08:00', '10:30'],
    ['Sistem Digital', 'MK107', 3, 1, 'Jumat', '13:00', '15:30'],
    
    // Semester 2 (7 Matkul)
    ['Struktur Data', 'MK201', 3, 2, 'Senin', '08:00', '10:30'],
    ['Aljabar Linear', 'MK202', 3, 2, 'Selasa', '08:00', '10:30'],
    ['Arsitektur Komputer', 'MK203', 3, 2, 'Selasa', '13:00', '15:30'],
    ['Pemrograman Berorientasi Objek', 'MK204', 3, 2, 'Rabu', '08:00', '10:30'],
    ['Statistika', 'MK205', 3, 2, 'Kamis', '08:00', '10:30'],
    ['Bahasa Indonesia', 'MK206', 2, 2, 'Kamis', '13:00', '14:40'],
    ['Basis Data', 'MK207', 3, 2, 'Jumat', '08:00', '10:30'],

    // Semester 3 (8 Matkul)
    ['Pemrograman Web', 'MK301', 3, 3, 'Senin', '08:00', '10:30'],
    ['Basis Data Lanjut', 'MK302', 3, 3, 'Senin', '13:00', '15:30'],
    ['Rekayasa Perangkat Lunak', 'MK303', 3, 3, 'Selasa', '08:00', '10:30'],
    ['Jaringan Komputer', 'MK304', 3, 3, 'Rabu', '08:00', '10:30'],
    ['Sistem Operasi', 'MK305', 3, 3, 'Rabu', '13:00', '15:30'],
    ['Kecerdasan Buatan', 'MK306', 3, 3, 'Kamis', '08:00', '10:30'],
    ['Interaksi Manusia Komputer', 'MK307', 3, 3, 'Kamis', '13:00', '15:30'],
    ['Etika Profesi', 'MK308', 3, 3, 'Jumat', '08:00', '10:30'],
];

$matkuls = [];
foreach($matkulData as $index => $md) {
    $matkuls[] = MataKuliah::create([
        'nama' => $md[0], 'kode' => $md[1], 'sks' => $md[2], 'semester' => $md[3],
        'hari' => $md[4], 'jam_mulai' => $md[5], 'jam_selesai' => $md[6],
        'dosen_id' => $dosens[$index % 5]->id, // Bagi rata ke 5 dosen
        'tahun_ajaran' => $tahunAjaran
    ]);
}

// 6. ENROLL KRS SISWA (Siswa hanya ambil matkul yang semesternya sama)
foreach($siswas as $siswa) {
    foreach($matkuls as $mk) {
        if ($mk->semester == $siswa->semester) {
            Krs::create([
                'siswa_id' => $siswa->id,
                'mata_kuliah_id' => $mk->id,
                'semester' => $mk->semester,
                'tahun_ajaran' => $tahunAjaran,
                'status' => 'aktif'
            ]);
        }
    }
}

// 7. BUAT TUGAS ACAK PER MATA KULIAH (1 MINGGU INI, 1 MINGGU DEPAN)
foreach($matkuls as $mk) {
    // Tugas 1: Minggu Ini (Acak hari 0-6, jam 08-20)
    $deadlineThisWeek = now()->startOfWeek()->addDays(rand(0, 6))->setTime(rand(8, 20), 0);
    Tugas::create([
        'mata_kuliah_id' => $mk->id,
        'nama' => "Tugas 1: Analisis " . $mk->nama,
        'bobot' => 10,
        'deadline' => $deadlineThisWeek->format('Y-m-d H:i:s'),
        'deskripsi' => "Harap kumpulkan analisa mingguan."
    ]);

    // Tugas 2: Minggu Depan (Acak hari 7-13, jam 08-20)
    $deadlineNextWeek = now()->startOfWeek()->addDays(rand(7, 13))->setTime(rand(8, 20), 0);
    Tugas::create([
        'mata_kuliah_id' => $mk->id,
        'nama' => "Tugas 2: Makalah " . $mk->nama,
        'bobot' => 20,
        'deadline' => $deadlineNextWeek->format('Y-m-d H:i:s'),
        'deskripsi' => "Pembuatan makalah akhir terkait."
    ]);
}

echo "Berhasil menyuntikkan 15 Siswa, 5 Dosen, 22 Matkul beserta Jadwal, dan Tugas secara acak!\n";
```
