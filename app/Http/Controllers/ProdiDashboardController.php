<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\UserSiswa;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProdiDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('prodi')->user();

        // Cache the heavy aggregate data for 15 minutes.
        // Invalidated implicitly by TTL — prodi data only changes when
        // tugas are created/deleted, which happens infrequently.
        $data = Cache::remember('prodi_dashboard', 900, function () {
            $weekStart = Carbon::now()->startOfDay();
            $weekEnd = Carbon::now()->addDays(6)->endOfDay();

            $totalSiswa = UserSiswa::count();
            $loadDistribution = BebanCalculator::weeklyLoadDistribution($weekStart, $weekEnd);
            $courseAverages = BebanCalculator::averageTasksPerWeekPerCourse();
            $weeklyTrend = BebanCalculator::prodiWeeklyTrend();

            $notifOverloadSks = Notifikasi::where('tipe', 'overload_sks')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $notifDeadlineCollision = Notifikasi::where('tipe', 'deadline_collision')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $notifOther = Notifikasi::whereNotIn('tipe', ['overload_sks', 'deadline_collision'])
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            return [
                'stats' => [
                    'total_siswa' => $totalSiswa,
                    'notif_overload_sks' => $notifOverloadSks,
                    'notif_deadline_collision' => $notifDeadlineCollision,
                    'notif_lainnya' => $notifOther,
                ],
                'distribution' => $loadDistribution,
                'trend' => $weeklyTrend,
                'courses' => $courseAverages,
            ];
        });

        return view('pages.prodi.⚡dashboard', array_merge(
            ['user' => $user],
            $data,
        ));
    }
}
