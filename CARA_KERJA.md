# EduTrack Daily — Dokumentasi Lengkap Cara Kerja

Dokumen ini menjelaskan **seluruh fitur** aplikasi EduTrack Daily, mulai dari alur login, fitur CRUD admin, fitur lengkap mahasiswa, fitur dosen, hingga fitur prodi dan sistem otomasi di background.

---

## Daftar Isi

1. [Arsitektur & Alur Login](#1-arsitektur--alur-login)
2. [Dashboard Admin](#2-dashboard-admin)
3. [Dashboard Mahasiswa (Siswa)](#3-dashboard-mahasiswa-siswa)
4. [Dashboard Dosen](#4-dashboard-dosen)
5. [Dashboard Prodi](#5-dashboard-prodi)
6. [Sistem Background (Scheduled Task & Queue)](#6-sistem-background-scheduled-task--queue)
7. [Struktur Database](#7-struktur-database)

---

## 1. Arsitektur & Alur Login

### 1.1 Multi-Guard Authentication

Aplikasi menggunakan **4 guard terpisah** — setiap role punya tabel user sendiri:

| Guard | Tabel | Model | Dashboard Route |
|-------|-------|-------|-----------------|
| `admin` | `user_admin` | `UserAdmin` | `/admin/dashboard` |
| `dosen` | `user_dosens` | `UserDosen` | `/dosen/dashboard` |
| `siswa` | `user_siswa` | `UserSiswa` | `/siswa/dashboard` |
| `prodi` | `user_prodis` | `UserProdi` | `/prodi/dashboard` |

### 1.2 Alur Login (`/login`)

1. User buka halaman `/login` (Livewire Volt component).
2. User masukkan **email** dan **password**.
3. Sistem mencoba login ke **semua guard secara berurutan**: admin → dosen → siswa → prodi.
4. Guard pertama yang cocok akan di-authenticate, lalu redirect ke dashboard yang sesuai.
5. Jika tidak cocok di guard manapun, tampilkan error "Email atau password salah."
6. Sistem juga otomatis memperbaiki **legacy plaintext password** — jika password di database belum di-hash, sistem akan hash dan update saat login pertama kali.

### 1.3 Rate Limiting

| Endpoint | Limit | Key |
|----------|-------|-----|
| Login (`/login`) | 5 request/menit | Per IP |
| Upload tugas siswa | 5 request/menit | Per user |
| Preview beban dosen | 10 request/menit | Per user |

---

## 2. Dashboard Admin

**URL:** `/admin/dashboard?resource=<nama_resource>`

Admin memiliki akses CRUD ke **semua data master** sistem melalui sidebar navigasi.

### 2.1 Sidebar Navigasi — Data Master

Admin bisa mengelola 13 resource berikut:

| Resource Key | Label | Deskripsi |
|-------------|-------|-----------|
| `admins` | Admin | Akun administrator (nama, email, password) |
| `dosens` | Dosen | Akun dosen (nama, email, password, NIDN, fakultas) |
| `siswas` | Siswa | Akun mahasiswa (nama, email, password, NIM, prodi, semester) |
| `mata-kuliah` | Mata Kuliah | Data mata kuliah (nama, kode, SKS, dosen pengampu, tahun ajaran, semester) |
| `dosen-pa` | Dosen PA | Pemetaan dosen pembimbing akademik ke mahasiswa |
| `ipk-history` | IPK History | Riwayat IPK per semester (IPK, total SKS, rekomendasi SKS) |
| `kalender-akademik` | Kalender Akademik | Event akademik (judul, tanggal, tipe, tahun ajaran) |
| `krs` | KRS | Kartu Rencana Studi (siswa, mata kuliah, semester, tahun ajaran, nilai) |
| `notifikasi` | Notifikasi Siswa | Notifikasi ke mahasiswa (judul, pesan, tipe, status baca) |
| `notifikasi-dosen` | Notifikasi Dosen | Notifikasi ke dosen (judul, pesan, tipe, mata kuliah, tugas) |
| `laporan` | Laporan | File laporan yang di-generate (judul, tipe, periode, file path) |
| `pengaturan` | Pengaturan | Key-value settings sistem |

### 2.2 CRUD Operasi (Berlaku untuk Semua Resource)

**Create:**
1. Admin pilih resource di sidebar.
2. Form "Tambah [Resource]" tampil di halaman.
3. Isi field sesuai konfigurasi (text, email, password, number, select, textarea, checkbox, date).
4. Field bertipe `select` menampilkan dropdown dari data terkait (misal: dosen_id → dropdown dosen).
5. Field `password` otomatis di-hash (`bcrypt`) sebelum disimpan.
6. Validasi: email/NIM/NIDN/kode unik, foreign key harus valid.
7. Klik "Tambah Data" → data tersimpan → redirect + flash message.

**Read:**
- Data ditampilkan dalam tabel dengan **pagination 10 per halaman**.
- Sidebar menampilkan **jumlah record** di setiap resource (di-cache 5 menit).
- Resource KRS dan IPK History punya **tampilan grouped** — data dikelompokkan per mahasiswa menggunakan `<details>` accordion.

**Update:**
1. Klik tombol "Edit" di baris data.
2. Form berubah jadi mode edit, terisi data existing.
3. Field password bisa dikosongkan (tidak wajib saat edit).
4. Klik "Simpan Perubahan" → data terupdate.
5. Link "Batal edit" untuk kembali ke mode tambah.

**Delete:**
1. Klik tombol "Hapus" di baris data.
2. Konfirmasi browser alert muncul.
3. Jika dikonfirmasi → data dihapus → redirect + flash message.

### 2.3 Fitur Khusus KRS: Tambah Paket KRS

Selain CRUD satuan, admin bisa menambahkan **KRS paket** sekaligus:

1. Pilih **Siswa** dari dropdown.
2. Pilih **Paket KRS** — paket dikelompokkan per "Semester X - Tahun Ajaran".
3. Isi status (default: "aktif").
4. Klik "Tambahkan Paket KRS" → semua mata kuliah dalam paket tersebut otomatis terdaftar di KRS siswa.
5. Jika mata kuliah sudah ada di KRS siswa, akan di-skip (tidak duplikat).
6. Feedback: "X dibuat, Y sudah ada."

### 2.4 Fitur Khusus IPK History: Kalkulasi IPK Otomatis

Admin bisa menghitung IPK mahasiswa otomatis dari data KRS:

1. Pilih **Siswa** dan input **Semester**.
2. Klik "Hitung & Simpan IPK".
3. Sistem mengambil semua KRS siswa di semester tersebut.
4. Mengecek kelengkapan **nilai huruf** — jika ada yang kosong, tampilkan error.
5. Menghitung IPK = Σ(Bobot × SKS) / Σ(SKS) dengan peta konversi:
   - A = 4.0, A- = 3.7, B+ = 3.3, B = 3.0, B- = 2.7, C+ = 2.3, C = 2.0, D = 1.0, E = 0.0
6. Otomatis set **rekomendasi SKS** semester depan:
   - IPK ≥ 3.5 → 24 SKS
   - IPK ≥ 3.0 → 22 SKS
   - IPK ≥ 2.75 → 20 SKS
   - IPK < 2.75 → 18 SKS
7. Data disimpan via `updateOrCreate` — jika sudah ada untuk siswa+semester, di-update.

### 2.5 Generate Laporan Akademik

**URL:** `/admin/laporan`

1. Admin set **Periode Mulai** dan **Periode Akhir** (tanggal).
2. Opsional: filter berdasarkan **Prodi**.
3. Klik "Generate Laporan".
4. Sistem menghitung secara batch (bukan loop per siswa):
   - Total mahasiswa
   - Rata-rata IPK (dari IPK terakhir per siswa)
   - Rata-rata SKS (dari KRS semester aktif)
   - Jumlah mahasiswa overload (>24 SKS)
   - Jumlah mahasiswa dengan deadline padat (≥3 tugas dalam periode)
5. Output: file HTML disimpan di `storage/app/public/laporans/`.
6. Record laporan tersimpan di tabel `laporan`.

---

## 3. Dashboard Mahasiswa (Siswa)

**URL:** `/siswa/dashboard?tab=<tab_name>`

Dashboard mahasiswa memiliki **7 tab** yang bisa diakses via sidebar navigasi.

### 3.1 Tab: Dashboard (Beranda)

Halaman utama dengan ringkasan beban akademik minggu ini.

**Komponen:**
- **Warning Banner** — Muncul jika status beban BERAT atau OVERLOAD. Warna oranye dengan pesan peringatan.
- **4 Stat Tiles:**
  - Total SKS Aktif (semester ini)
  - Tugas Pekan Fokus (jumlah tugas minggu ini yang belum disubmit)
  - Deadline 3 Hari (jumlah tugas deadline dalam 3 hari ke depan)
  - Status Beban (Ringan/Normal/Berat/Overload)
- **Bar Chart Distribusi Beban Mingguan** — 7 bar (Senin–Minggu) menunjukkan jumlah tugas per hari. Warna bar mengikuti status beban.
- **Tugas Mendatang** — 4 tugas terdekat yang belum lewat deadline, dengan status urgency:
  - 🔴 Critical: hari ini atau besok
  - 🟠 Warning: 2–3 hari lagi
  - 🟢 Safe: >3 hari

**Logika Beban:**
- Dihitung dari jumlah tugas **yang belum disubmit** dalam 1 minggu (Senin–Minggu).
- ≤1 tugas = Ringan, 2 = Normal, 3 = Berat, ≥4 = Overload.

### 3.2 Tab: Calendar (Kalender)

Kalender akademik interaktif.

**Komponen:**
- **Kalender bulanan** — Navigasi bulan sebelumnya/berikutnya.
  - Setiap sel tanggal menunjukkan jumlah tugas.
  - Warna sel berdasarkan beban hari itu (hijau/kuning/oranye/merah).
  - Klik tanggal untuk lihat detail.
- **Panel Detail Hari** — Menampilkan timeline deadline tugas di hari yang dipilih (jam, judul tugas, mata kuliah).
- **Tugas Terlambat** — Daftar tugas yang sudah melewati deadline dan belum disubmit, lengkap dengan info keterlambatan.

### 3.3 Tab: Monitoring (Pemantauan Akademik)

Tabel lengkap mata kuliah semester ini.

**Komponen:**
- **3 Stat Tiles:**
  - SKS Aktif (semester ini)
  - SKS Lulus (akumulasi semester sebelumnya)
  - IPK Kumulatif (rata-rata semua semester)
- **Tabel Mata Kuliah** — Kolom:
  - Nama & kode mata kuliah
  - Detail nilai per tugas (nama tugas, bobot %, nilai)
  - SKS
  - Total Tugas
  - Tugas Minggu Ini
  - Badge Status Beban (Ringan/Normal/Berat/Overload)
  - Status (Aktif/Selesai)

### 3.4 Tab: Analytics (Analitik)

Prediksi risiko dan rekomendasi akademik berbasis data.

**Komponen:**
- **Risk Score** — Skor risiko 0–100% dihitung dari:
  - Beban tugas mingguan (max 45 poin, 12 poin per tugas)
  - Urgency tugas 3 hari ke depan (max 25 poin, 8 poin per tugas)
  - IPK (IPK <2.75 = +20 poin, IPK <3.0 = +12 poin)
  - Penurunan IPK dari semester sebelumnya (max +10 poin)
- **Rekomendasi SKS** — Saran jumlah SKS semester depan berdasarkan:
  - Jika ada rekomendasi dari riwayat IPK admin, digunakan.
  - Jika tidak, dihitung dari risk score + IPK terakhir.
- **Grafik IPK (SVG Line Chart)** — Tren IPK per semester dengan smooth bezier curve, gradient fill, hover tooltip.
- **Kompetensi Mata Kuliah** — Progress bar per mata kuliah berdasarkan rata-rata nilai tugas:
  - ≥85 = Sangat Baik
  - ≥75 = Baik
  - ≥65 = Cukup
  - <65 = Perlu Pendampingan

### 3.5 Tab: Notifications (Notifikasi)

Daftar 5 notifikasi terbaru.

**Tipe notifikasi:**
- `peringatan` / `warning` — Warna merah, border kiri merah.
- `pengingat` / `reminder` — Warna oranye, border kiri oranye.
- `sukses` / `success` — Warna hijau, border kiri hijau.
- `info` / `informasi` — Warna biru, border kiri biru.

**Fitur:**
- Notifikasi belum dibaca ditandai dengan **dot biru berkedip** (animate-pulse).
- Timestamp dalam format relatif bahasa Indonesia ("2 jam yang lalu").

### 3.6 Tab: Tugas (Pengumpulan Tugas)

Halaman untuk mengumpulkan tugas per mata kuliah.

**Alur:**
1. **Pilih Mata Kuliah** — Daftar mata kuliah aktif dengan badge:
   - Badge merah: "X belum dikumpul"
   - Badge hijau: "Semua tugas selesai"
2. **Lihat Daftar Tugas** — Setelah pilih MK, tampil semua tugas dengan info:
   - Nama tugas, deadline (format tanggal Indonesia + jam), bobot (%).
   - Status: Selesai (hijau), Terlambat (merah), atau Belum (kuning).
3. **Upload Submission:**
   - Input file PDF only (max 10 MB).
   - Validasi: ekstensi `.pdf` + cek header file (`%PDF-`).
   - Klik "Submit PDF" → file disimpan di `storage/app/submissions/`.
   - Jika sudah pernah submit → tombol berubah jadi "Ganti PDF", file lama dihapus.
   - Jika submit setelah deadline → status otomatis "late".
4. **Download Submission** — Bisa download file yang sudah disubmit.

**Cache Invalidation:**
- Setelah submit tugas, cache dashboard siswa (`siswa_dashboard_{id}`) dan cache preview dosen (`dosen_preview_{id}`) otomatis di-bust.

### 3.7 Tab: Profile (Profil)

Informasi pribadi dan ringkasan akademik.

**Komponen:**
- **Kartu Identitas:**
  - Avatar circle (inisial nama)
  - Nama, NIM
  - Program Studi, Email, Angkatan/Semester
  - **Prediksi Lulus** (jika semester ≥ 2):
    - Sisa SKS (target 144 SKS)
    - Rata-rata SKS per semester
    - Estimasi semester kelulusan
- **Ringkasan Akademik:**
  - 4 stat: IPK Kumulatif, SKS Lulus, SKS Kontrak, Semester
  - Dosen Pembimbing Akademik (dari relasi DosenPa terbaru)
- **Status Akun:** Role dan status akademik.

### 3.8 Fitur: Pengaturan Notifikasi

**URL:** `POST /siswa/preferences`

Siswa bisa mengatur preferensi notifikasi (on/off per tipe). Data disimpan di kolom `notification_preferences` (JSON) di tabel `user_siswa`.

---

## 4. Dashboard Dosen

**URL:** `/dosen/dashboard?tab=<tab_name>`

Dashboard dosen memiliki **4 tab**.

### 4.1 Tab: Kelas

**Sub-view A — Daftar Mata Kuliah (tanpa parameter `mk`)**

Grid kartu mata kuliah yang diajar dosen. Setiap kartu menampilkan:
- Kode mata kuliah
- Nama mata kuliah
- Jumlah SKS dan total tugas
- Klik untuk masuk ke detail kelas.

**Sub-view B — Detail Kelas (dengan parameter `mk=<id>`)**

Halaman lengkap per mata kuliah dengan 2 section utama:

#### Section 1: Tambah Tugas

Form untuk membuat tugas baru:

1. **Input:** Nama tugas, Deadline (tanggal + jam + menit), Deskripsi.
2. **Aggregate Preview** — Menampilkan kartu preview beban per mata kuliah yang diajar (jumlah mahasiswa, rata-rata tugas, status terberat). Data di-cache 1 jam.
3. **Live Preview Beban** (JavaScript):
   - Saat dosen mengisi deadline, sistem otomatis fetch ke `POST /dosen/tugas/preview-beban`.
   - Menampilkan panel real-time: jumlah mahasiswa terdampak, rata-rata tugas, status terberat.
   - Jika beban berat/overload → tampilkan **warning** + **saran reschedule** (tanggal alternatif dengan beban lebih ringan, dicari dalam 14 hari ke depan).
   - Tabel per mahasiswa: nama, NIM, jumlah tugas saat ini vs jika tugas disimpan, status projected.
4. **Override Checkbox** — Jika beban berat/overload, dosen harus centang "Tetap lanjut" atau ubah deadline.
5. **Saat disimpan:**
   - Bobot tugas otomatis di-rebalance (100% dibagi rata ke semua tugas di MK tersebut).
   - Notifikasi beban naik dikirim ke mahasiswa terdampak via **queue job** (async, tidak blocking).
   - Jika status berat/overload → notifikasi dosen juga dibuat.
   - Cache preview dosen + cache dashboard siswa di-bust.

#### Section 2: Daftar Tugas & Nilai

List semua tugas di mata kuliah ini:

- **Per tugas:** Nama, deadline, bobot (%), badge status beban, tombol hapus.
- **Dropdown Mahasiswa** (`<details>` accordion) — Klik untuk expand tabel penilaian:
  - Kolom: Nama Mahasiswa, NIM, Input Nilai (0–100), Komentar, Tombol Simpan/Update, File Submission.
  - **Input Nilai:** Nilai dan komentar bisa diisi per mahasiswa per tugas. `updateOrCreate` — jika sudah ada, di-update.
  - **Recalc Nilai Akhir:** Setiap kali nilai disimpan, sistem otomatis menghitung ulang `nilai_akhir` di KRS:
    - Formula: `Σ(nilai × bobot) / Σ(bobot)` (hanya tugas yang sudah dinilai).
    - Konversi ke huruf: A (≥85), A- (≥80), B+ (≥75), B (≥70), B- (≥65), C+ (≥60), C (≥55), D (≥50), E (<50).
  - **File Submission:** Jika mahasiswa sudah submit → link download + status (Tepat waktu / Terlambat + timestamp). Jika belum → "Belum submit" merah.

### 4.2 Tab: Beban

Monitoring beban tugas mahasiswa bimbingan.

**Komponen:**
- **PA Risk Cards** — Grid kartu mahasiswa bimbingan (Dosen PA), diurutkan berdasarkan risk score tertinggi. Setiap kartu: nama, NIM, risk score (%), jumlah tugas, status beban. Warna kartu mengikuti status beban.
- **Filter Mata Kuliah** — Dropdown untuk memilih mata kuliah (jika mengajar >1).
- **Tabel Beban per Mata Kuliah:**
  - Kolom: Nama, NIM, Minggu Ini (count + status badge), Minggu Depan (count + status badge), Bimbingan (✓ jika mahasiswa tersebut adalah mahasiswa bimbingan dosen).
  - Data menggabungkan `thisWeek` dan `nextWeek` workload dari `BebanCalculator::weeklyLoadForCourse()`.

### 4.3 Tab: Notifikasi

Daftar notifikasi dosen dengan **pagination 10 per halaman**.

**Fitur:**
- Notifikasi belum dibaca: background biru + border kiri dark.
- Notifikasi sudah dibaca: background netral.
- Badge "Beban Tinggi" merah jika tipe = `beban_tinggi`.
- Tombol "Tandai Dibaca" → `PATCH /dosen/notifikasi/{id}/read`.
- **Badge unread count** di sidebar navigasi (merah, angka notifikasi belum dibaca).

### 4.4 Tab: Profil

Informasi profil dosen.

**Komponen:**
- **Kartu Identitas:** Avatar inisial, nama, NIDN, email, fakultas.
- **Ringkasan:** 2 stat besar (Mata Kuliah Diajar, Mahasiswa Bimbingan).
- **Daftar Mata Kuliah:** List semua mata kuliah yang diajar (nama, kode, SKS).
- **Status Akun:** Role dan status.

---

## 5. Dashboard Prodi

**URL:** `/prodi/dashboard`

Dashboard untuk Ketua Program Studi — menampilkan overview beban seluruh mahasiswa.

**Komponen:**
- **Statistik:**
  - Total mahasiswa
  - Jumlah notifikasi Overload SKS (30 hari terakhir)
  - Jumlah notifikasi Deadline Collision (30 hari terakhir)
  - Jumlah notifikasi lainnya (30 hari terakhir)
- **Distribusi Beban Mingguan** — Jumlah mahasiswa per kategori beban (Ringan/Normal/Berat/Overload) untuk minggu ini.
- **Tren Beban 8 Minggu** — Grafik tren distribusi beban 8 minggu terakhir. Dihitung dalam 1 query (bukan 8 query terpisah).
- **Tabel Rata-rata Tugas per Mata Kuliah** — Rata-rata jumlah tugas per minggu per mata kuliah, dengan status badge.

**Caching:** Seluruh data Prodi dashboard di-cache 15 menit (`prodi_dashboard`).

---

## 6. Sistem Background (Scheduled Task & Queue)

### 6.1 Scheduled Command: `beban:check`

**Jadwal:** Setiap hari pukul **07:00** (didaftarkan di `routes/console.php`).

**Alur:**
1. Ambil semua mahasiswa dalam chunk 100.
2. Per chunk, hitung secara batch:
   - **Total SKS aktif** per mahasiswa (join KRS + mata kuliah).
   - **Jumlah tugas 7 hari ke depan** per mahasiswa (join KRS + mata kuliah + tugas).
3. Untuk setiap mahasiswa:
   - Jika **SKS > 24** → buat notifikasi siswa "Beban SKS Overload" + notifikasi ke Dosen PA.
   - Jika **tugas ≥ 3 dalam 7 hari** → buat notifikasi siswa "Deadline Padat" + notifikasi ke Dosen PA.

### 6.2 Queue Job: `SendBebanNaikNotifications`

**Dispatch:** Setiap kali dosen membuat tugas baru (`storeTugas`).

**Alur:**
1. Ambil semua mahasiswa terdaftar di mata kuliah terkait.
2. Hitung beban sebelum dan sesudah tugas baru (batch query, bukan loop).
3. Jika status beban mahasiswa **naik** (misal: Ringan → Normal, Normal → Berat):
   - Buat notifikasi "Beban Minggu Ini Naik: [Level]" untuk mahasiswa tersebut.
4. Insert semua notifikasi sekaligus (bulk insert, chunk 500).
5. Bust cache dashboard siswa yang terdampak.

**Konfigurasi Queue:**
- Driver: `database` (production) atau `sync` (local development).
- Retry: 3 kali (`$tries = 3`).

---

## 7. Struktur Database

### 7.1 Tabel User

| Tabel | Field Kunci | Unique |
|-------|------------|--------|
| `user_admin` | name, email, password | email |
| `user_dosens` | name, email, password, nidn, fakultas | email, nidn |
| `user_siswa` | name, email, password, nim, prodi, semester, notification_preferences, profile_completed | email, nim |
| `user_prodis` | name, email, password | email |

### 7.2 Tabel Akademik

| Tabel | Relasi | Deskripsi |
|-------|--------|-----------|
| `mata_kuliah` | → user_dosens (dosen_id) | Kode, nama, SKS, tahun ajaran, semester |
| `krs` | → user_siswa, → mata_kuliah | Semester, tahun ajaran, nilai_akhir, nilai_huruf, status |
| `tugas` | → mata_kuliah | Nama, bobot, deadline (datetime), deskripsi, status_beban, override |
| `tugas_submission` | → tugas, → user_siswa | File path, file name, submitted_at, status (submitted/late) |
| `nilai_tugas` | → tugas, → user_siswa | Nilai (0-100), komentar |
| `ipk_history` | → user_siswa | Semester, IPK, total SKS, tahun ajaran, rekomendasi SKS |
| `dosen_pa` | → user_dosens, → user_siswa | Tahun ajaran (mapping dosen PA ↔ mahasiswa) |

### 7.3 Tabel Notifikasi

| Tabel | Relasi | Deskripsi |
|-------|--------|-----------|
| `notifikasi` | → user_siswa | Judul, pesan, tipe, sumber, is_read |
| `notifikasi_dosen` | → user_dosens, → mata_kuliah, → tugas | Judul, pesan, tipe, sumber, is_read |

### 7.4 Tabel Lainnya

| Tabel | Deskripsi |
|-------|-----------|
| `kalender_akademik` | Event akademik (judul, tanggal, tipe) |
| `laporan` | Laporan yang di-generate (judul, tipe, periode, file path) |
| `pengaturan` | Key-value settings sistem |

### 7.5 Index Database

Index yang sudah diterapkan untuk performa:

| Tabel | Index | Query yang dioptimasi |
|-------|-------|-----------------------|
| `tugas` | `deadline` | Filter tugas per minggu |
| `tugas` | `(mata_kuliah_id, deadline)` | Weekly load queries |
| `krs` | `(siswa_id, mata_kuliah_id, semester)` | KRS lookup per siswa |
| `notifikasi` | `(tipe, created_at)` | Prodi dashboard count |
| `notifikasi` | `(siswa_id, created_at)` | Siswa notifikasi listing |
| `notifikasi_dosen` | `(dosen_id, created_at)` | Dosen notifikasi listing |
| `nilai_tugas` | `(tugas_id, siswa_id)` UNIQUE | Nilai lookup & updateOrCreate |
| `ipk_history` | `(siswa_id, semester)` | IPK history lookup |
| `tugas_submission` | `(tugas_id, siswa_id)` UNIQUE | Submission lookup |

### 7.6 Caching Strategy

| Cache Key | TTL | Isi | Invalidation |
|-----------|-----|-----|--------------|
| `siswa_dashboard_{id}` | 10 menit | Core dashboard data (profile, matakuliah, tugas, workload, analytics, notifikasi) | Submit tugas, dosen create/delete tugas, dosen kasih nilai, job notifikasi |
| `dosen_preview_{id}` | 1 jam | Aggregate preview beban per mata kuliah | Dosen create/delete tugas, siswa submit tugas |
| `prodi_dashboard` | 15 menit | Seluruh data prodi dashboard | TTL-based (expire natural) |
| `admin_resource_counts` | 5 menit | COUNT per resource (13 tabel) | Admin create/update/delete any resource |

---

## Catatan Teknis

- **Framework:** Laravel 13.16.1, PHP 8.3
- **Frontend:** Tailwind CSS v4 (via Vite plugin), Livewire (login page)
- **Database:** MySQL (production), SQLite (testing)
- **Queue:** Database driver (production), Sync driver (local)
- **Cache:** Database driver
- **Session:** Database driver
- **File Upload:** Local disk (`storage/app/submissions/`), PDF only, max 10 MB
- **Font:** Instrument Sans (via Bunny Fonts)
