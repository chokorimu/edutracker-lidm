<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\UserSiswa;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProdiDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('prodi')->user();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Student count
        $totalSiswa = UserSiswa::count();

        // Number of students per load category
        $loadDistribution = BebanCalculator::weeklyLoadDistribution($weekStart, $weekEnd);

        // Course average tasks per week
        $courseAverages = BebanCalculator::averageTasksPerWeekPerCourse();

        // Notification breakdown in last 30 days
        $notifOverloadSks = Notifikasi::where('tipe', 'overload_sks')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $notifDeadlineCollision = Notifikasi::where('tipe', 'deadline_collision')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $notifOther = Notifikasi::whereNotIn('tipe', ['overload_sks', 'deadline_collision'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('pages.prodi.⚡dashboard', [
            'user' => $user,
            'stats' => [
                'total_siswa' => $totalSiswa,
                'notif_overload_sks' => $notifOverloadSks,
                'notif_deadline_collision' => $notifDeadlineCollision,
                'notif_lainnya' => $notifOther,
            ],
            'distribution' => $loadDistribution,
            'courses' => $courseAverages,
        ]);
    }
}
