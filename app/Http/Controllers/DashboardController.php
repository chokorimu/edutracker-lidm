<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('pages.admin.⚡dashboard', [
            'user' => Auth::guard('admin')->user(),
        ]);
    }

    public function dosen(): View
    {
        return view('pages.dosen.⚡dashboard', [
            'user' => Auth::guard('dosen')->user(),
        ]);
    }

    public function siswa(Request $request): View
    {
        $tabs = ['dashboard', 'calendar', 'monitoring', 'analytics', 'notifications', 'profile'];
        $currentTab = $request->query('tab', 'dashboard');

        if (! in_array($currentTab, $tabs, true)) {
            $currentTab = 'dashboard';
        }

        return view('pages.siswa.⚡dashboard', [
            'currentTab' => $currentTab,
            'data' => $this->siswaDashboardData(),
        ]);
    }

    public function logoutAdmin(Request $request): RedirectResponse
    {
        return $this->logout($request, 'admin');
    }

    public function logoutDosen(Request $request): RedirectResponse
    {
        return $this->logout($request, 'dosen');
    }

    public function logoutSiswa(Request $request): RedirectResponse
    {
        return $this->logout($request, 'siswa');
    }

    private function logout(Request $request, string $guard): RedirectResponse
    {
        Auth::guard($guard)->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }

    private function siswaDashboardData(): array
    {
        $user = Auth::guard('siswa')->user();

        return [
            'profile' => [
                'nim' => $user->nim ?? '203480234',
                'nama' => $user->name ?? 'Muhammad Hasan',
                'email' => $user->email ?? 'muhammad.hasan@university.ac.id',
                'prodi' => 'Teknik Informatika',
                'semester' => 5,
                'angkatan' => 2022,
                'ipk' => 3.45,
                'sks_lulus' => 96,
                'sks_semester' => 21,
                'dosen_pa' => 'Dr. Rahmat Hidayat, S.Kom., M.T.',
            ],
            'matakuliah' => [
                ['nama' => 'Algoritma & Struktur Data', 'sks' => 4, 'tugas' => 5, 'beban' => 'Tinggi', 'status' => 'Aktif'],
                ['nama' => 'Basis Data', 'sks' => 3, 'tugas' => 3, 'beban' => 'Sedang', 'status' => 'Aktif'],
                ['nama' => 'Sistem Operasi', 'sks' => 3, 'tugas' => 4, 'beban' => 'Tinggi', 'status' => 'Aktif'],
                ['nama' => 'Jaringan Komputer', 'sks' => 3, 'tugas' => 2, 'beban' => 'Ringan', 'status' => 'Aktif'],
                ['nama' => 'Kalkulus 2', 'sks' => 3, 'tugas' => 3, 'beban' => 'Sedang', 'status' => 'Aktif'],
                ['nama' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'tugas' => 4, 'beban' => 'Sedang', 'status' => 'Aktif'],
                ['nama' => 'Statistika', 'sks' => 2, 'tugas' => 2, 'beban' => 'Ringan', 'status' => 'Aktif'],
            ],
            'tugas_mendatang' => [
                ['judul' => 'Tugas Algoritma & Struktur Data', 'matkul' => 'Algoritma & Struktur Data', 'deadline' => '15 Mei 2026', 'sisa' => '1 hari lagi', 'jam' => '23:59', 'status' => 'critical'],
                ['judul' => 'Paper Review Database', 'matkul' => 'Basis Data', 'deadline' => '17 Mei 2026', 'sisa' => '3 hari lagi', 'jam' => '23:58', 'status' => 'warning'],
                ['judul' => 'Presentasi Sistem Operasi', 'matkul' => 'Sistem Operasi', 'deadline' => '20 Mei 2026', 'sisa' => '6 hari lagi', 'jam' => '14:00', 'status' => 'safe'],
                ['judul' => 'Quiz Online', 'matkul' => 'Jaringan Komputer', 'deadline' => '22 Mei 2026', 'sisa' => '8 hari lagi', 'jam' => '10:00', 'status' => 'info'],
            ],
            'notifikasi' => [
                ['tipe' => 'peringatan', 'judul' => 'Deadline Collision Terdeteksi', 'desc' => 'Terdapat 3 tugas dengan deadline yang sama pada 15 Mei 2026.', 'waktu' => '2 jam yang lalu', 'unread' => true],
                ['tipe' => 'pengingat', 'judul' => 'Deadline Tugas Mendekat', 'desc' => 'Tugas Algoritma & Struktur Data akan berakhir dalam 1 hari.', 'waktu' => '3 jam yang lalu', 'unread' => true],
                ['tipe' => 'sukses', 'judul' => 'Distribusi Beban Ideal', 'desc' => 'Distribusi beban minggu depan berada dalam kategori normal.', 'waktu' => '4 jam yang lalu', 'unread' => true],
                ['tipe' => 'informasi', 'judul' => 'Tugas Baru Ditambahkan', 'desc' => 'Dosen telah menambahkan tugas baru untuk mata kuliah Basis Data.', 'waktu' => '1 hari yang lalu', 'unread' => false],
                ['tipe' => 'peringatan', 'judul' => 'Beban Akademik Tinggi', 'desc' => 'Beban akademik Anda minggu ini melebihi rata-rata.', 'waktu' => '1 hari yang lalu', 'unread' => false],
            ],
        ];
    }
}
