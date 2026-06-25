<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\UserSiswa;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProdiDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('prodi')->user();

        // Simple aggregation for dashboard
        $totalSiswa = UserSiswa::count();
        $totalNotifikasi = Notifikasi::where('created_at', '>=', now()->subDays(30))->count();
        $mkCount = MataKuliah::count();

        return view('pages.prodi.⚡dashboard', [
            'user' => $user,
            'stats' => [
                'total_siswa' => $totalSiswa,
                'total_notifikasi_30d' => $totalNotifikasi,
                'total_matkul' => $mkCount,
            ]
        ]);
    }
}
