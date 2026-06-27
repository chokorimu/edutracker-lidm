<?php

namespace App\Http\Controllers;

use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NotifikasiDosen;
use App\Models\Tugas;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DosenResourceController extends Controller
{
    private const TABS = ['tugas', 'beban', 'notifikasi', 'profil'];

    public function index(Request $request): View
    {
        $user = Auth::guard('dosen')->user();
        $tab = $request->query('tab', 'tugas');

        if (! in_array($tab, self::TABS, true)) {
            $tab = 'tugas';
        }

        $data = [];

        switch ($tab) {
            case 'tugas':
                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)->get();
                $mkIds = $data['mataKuliahList']->pluck('id');
                $data['aggregatePreview'] = BebanCalculator::aggregatePreviewForDosen($user);
                $data['tugasList'] = Tugas::whereIn('mata_kuliah_id', $mkIds)
                    ->with('mataKuliah')
                    ->latest()
                    ->paginate(20)
                    ->withQueryString();
                break;

            case 'beban':
                $weekStart = Carbon::now()->startOfWeek();
                $weekEnd = Carbon::now()->endOfWeek();
                $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
                $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();

                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)->with('krs.siswa')->get();
                $data['bimbinganIds'] = DosenPa::where('dosen_id', $user->id)->pluck('siswa_id')->toArray();
                $data['weekStart'] = $weekStart;
                $data['weekEnd'] = $weekEnd;
                $data['nextWeekStart'] = $nextWeekStart;
                $data['nextWeekEnd'] = $nextWeekEnd;
                $data['paRiskCards'] = BebanCalculator::paRiskCards($user);

                $data['workloadData'] = $data['mataKuliahList']->map(function ($mk) use ($weekStart, $weekEnd, $nextWeekStart, $nextWeekEnd) {
                    return [
                        'nama' => $mk->nama,
                        'kode' => $mk->kode,
                        'thisWeek' => BebanCalculator::weeklyLoadForCourse($mk->id, $weekStart, $weekEnd),
                        'nextWeek' => BebanCalculator::weeklyLoadForCourse($mk->id, $nextWeekStart, $nextWeekEnd),
                    ];
                });
                break;

            case 'notifikasi':
                $data['notifikasiList'] = NotifikasiDosen::where('dosen_id', $user->id)
                    ->with('mataKuliah', 'tugas')
                    ->latest()
                    ->paginate(10)
                    ->withQueryString();
                break;

            case 'profil':
                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)->get();
                $data['bimbinganCount'] = DosenPa::where('dosen_id', $user->id)->count();
                break;
        }

        return view('pages.dosen.⚡dashboard', [
            'user' => $user,
            'currentTab' => $tab,
            'data' => $data,
        ]);
    }

    public function storeTugas(Request $request): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'deadline' => 'required|date|after:now',
            'override' => 'nullable|boolean',
        ]);

        $mk = MataKuliah::findOrFail($validated['mata_kuliah_id']);
        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak menambah tugas di mata kuliah ini.');
        }

        $tugas = new Tugas;
        $tugas->mata_kuliah_id = $validated['mata_kuliah_id'];
        $tugas->nama = $validated['nama'];
        $tugas->deskripsi = $validated['deskripsi'] ?? null;
        $tugas->bobot = $validated['bobot'] ?? null;
        $tugas->deadline = $validated['deadline'];
        $tugas->status_beban = $this->computeStatusBeban($mk->id, $validated['deadline']);
        $tugas->override = $request->boolean('override');

        if (! $request->boolean('override') && in_array($tugas->status_beban, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true)) {
            $suggestions = BebanCalculator::rescheduleSuggestions($mk->id, $validated['deadline']);

            return back()
                ->withInput()
                ->with('deadline_suggestions', $suggestions)
                ->withErrors([
                    'beban_warning' => "Peringatan: Beban tugas ini tergolong {$tugas->status_beban}. Silakan ubah deadline atau centang 'tetap lanjut' untuk tetap menyimpan.",
                ]);
        }

        $tugas->save();

        if (in_array($tugas->status_beban, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true)) {
            NotifikasiDosen::create([
                'dosen_id' => $user->id,
                'mata_kuliah_id' => $mk->id,
                'tugas_id' => $tugas->id,
                'judul' => "Beban tinggi: {$tugas->nama}",
                'pesan' => "Tugas {$tugas->nama} di {$mk->nama} tergolong {$tugas->status_beban} untuk mahasiswa bimbingan Anda.",
                'tipe' => 'beban_tinggi',
                'sumber' => 'system',
            ]);
        }

        return redirect()->route('dosen.dashboard', ['tab' => 'tugas'])
            ->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function updateTugas(Request $request, int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $tugas = Tugas::findOrFail($id);
        $mk = $tugas->mataKuliah;

        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengubah tugas ini.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'deadline' => 'required|date',
            'status' => 'required|in:aktif,selesai',
        ]);

        $tugas->fill($validated);
        $tugas->status_beban = $this->computeStatusBeban($mk->id, $validated['deadline'], $tugas->id);
        $tugas->save();

        return redirect()->route('dosen.dashboard', ['tab' => 'tugas'])
            ->with('status', 'Tugas diperbarui.');
    }

    public function destroyTugas(int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $tugas = Tugas::findOrFail($id);

        if ($tugas->mataKuliah->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus tugas ini.');
        }

        $tugas->delete();

        return redirect()->route('dosen.dashboard', ['tab' => 'tugas'])
            ->with('status', 'Tugas dihapus.');
    }

    public function markNotifikasiRead(int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $notif = NotifikasiDosen::where('dosen_id', $user->id)->findOrFail($id);
        $notif->update(['is_read' => true]);

        return redirect()->route('dosen.dashboard', ['tab' => 'notifikasi'])
            ->with('status', 'Notifikasi dibaca.');
    }

    private function computeStatusBeban(int $mataKuliahId, string $deadline, ?int $excludeTugasId = null): string
    {
        $deadlineDate = Carbon::parse($deadline);
        $weekStart = (clone $deadlineDate)->startOfWeek();
        $weekEnd = (clone $deadlineDate)->endOfWeek();

        $siswaIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return BebanCalculator::LIGHT;
        }

        $courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)
            ->get(['siswa_id', 'mata_kuliah_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('mata_kuliah_id'));

        $allCourseIds = $courseIdsBySiswa->flatten()->unique()->values();
        $tugasCountByCourse = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->when($excludeTugasId, fn ($q) => $q->where('id', '!=', $excludeTugasId))
            ->get(['mata_kuliah_id'])
            ->groupBy('mata_kuliah_id')
            ->map->count();

        $worst = BebanCalculator::LIGHT;
        $severity = [
            BebanCalculator::LIGHT => 0,
            BebanCalculator::NORMAL => 1,
            BebanCalculator::HEAVY => 2,
            BebanCalculator::OVERLOAD => 3,
        ];

        foreach ($siswaIds as $siswaId) {
            $studentCourseIds = $courseIdsBySiswa->get($siswaId, collect());

            $count = $studentCourseIds->sum(fn ($courseId) => $tugasCountByCourse->get($courseId, 0)) + 1;

            $status = BebanCalculator::forCount($count);

            if ($status === BebanCalculator::OVERLOAD) {
                return BebanCalculator::OVERLOAD;
            }
            if ($severity[$status] > $severity[$worst]) {
                $worst = $status;
            }
        }

        return $worst;
    }
}
