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
                $data['tugasList'] = Tugas::whereIn('mata_kuliah_id', $mkIds)
                    ->with('mataKuliah')
                    ->latest()
                    ->paginate(20)
                    ->withQueryString();
                break;

            case 'beban':
                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)->with('krs.siswa')->get();
                $data['bimbinganIds'] = DosenPa::where('dosen_id', $user->id)->pluck('siswa_id')->toArray();
                $data['weekStart'] = Carbon::now()->startOfWeek();
                $data['weekEnd'] = Carbon::now()->endOfWeek();
                $data['nextWeekStart'] = Carbon::now()->addWeek()->startOfWeek();
                $data['nextWeekEnd'] = Carbon::now()->addWeek()->endOfWeek();
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

        // Validate ownership
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

        // If heavy/overload and no override, warn and redirect back
        if (! $request->boolean('override') && in_array($tugas->status_beban, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true)) {
            return back()
                ->withInput()
                ->withErrors([
                    'beban_warning' => "Peringatan: Beban tugas ini tergolong {$tugas->status_beban}. ".
                        'Silakan ubah deadline atau centang "tetap lanjut" untuk tetap menyimpan.',
                ]);
        }

        $tugas->save();

        // Auto‑generate notification for DosenPa students if overload
        if (in_array($tugas->status_beban, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true)) {
            $bimbinganSiswaIds = DosenPa::where('dosen_id', $user->id)->pluck('siswa_id');
            foreach ($bimbinganSiswaIds as $siswaId) {
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
        }

        return redirect()->route('dosen.dashboard', ['tab' => 'tugas'])
            ->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function updateTugas(Request $request, int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $tugas = Tugas::findOrFail($id);

        // Ownership via mata_kuliah
        $mk = $tugas->mataKuliah;
        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengubah tugas ini.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'deadline' => 'required|date',
            'override' => 'nullable|boolean',
        ]);

        $tugas->nama = $validated['nama'];
        $tugas->deskripsi = $validated['deskripsi'] ?? null;
        $tugas->bobot = $validated['bobot'] ?? null;
        $tugas->deadline = $validated['deadline'];
        $tugas->status_beban = $this->computeStatusBeban($mk->id, $validated['deadline']);
        $tugas->override = $request->boolean('override');

        if (! $request->boolean('override') && in_array($tugas->status_beban, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true)) {
            return back()
                ->withInput()
                ->withErrors([
                    'beban_warning' => "Peringatan: Beban tugas ini tergolong {$tugas->status_beban}. ".
                        'Silakan ubah deadline atau centang "tetap lanjut" untuk tetap menyimpan.',
                ]);
        }

        $tugas->save();

        return redirect()->route('dosen.dashboard', ['tab' => 'tugas'])
            ->with('status', 'Tugas berhasil diperbarui.');
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
            ->with('status', 'Tugas berhasil dihapus.');
    }

    public function markNotifikasiRead(int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $notif = NotifikasiDosen::where('dosen_id', $user->id)->findOrFail($id);
        $notif->update(['is_read' => true]);

        return redirect()->route('dosen.dashboard', ['tab' => 'notifikasi'])
            ->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Compute aggregate load for the deadline week.
     * Picks the worst load among all students enrolled in this course.
     */
    private function computeStatusBeban(int $mataKuliahId, string $deadline): string
    {
        $deadlineDate = Carbon::parse($deadline);
        $weekStart = (clone $deadlineDate)->startOfWeek();
        $weekEnd = (clone $deadlineDate)->endOfWeek();

        $siswaIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return BebanCalculator::LIGHT;
        }

        $worst = BebanCalculator::LIGHT;

        foreach ($siswaIds as $siswaId) {
            // Get all course IDs this student is enrolled in (any semester)
            $studentCourseIds = Krs::where('siswa_id', $siswaId)->pluck('mata_kuliah_id');

            // Count tasks across all those courses falling in the same week
            $count = Tugas::whereIn('mata_kuliah_id', $studentCourseIds)
                ->whereBetween('deadline', [$weekStart, $weekEnd])
                ->count();

            $load = BebanCalculator::forCount($count);
            $worst = $this->worstLoad($worst, $load);
        }

        return $worst;
    }

    private function worstLoad(string $current, string $candidate): string
    {
        $order = [
            BebanCalculator::LIGHT => 0,
            BebanCalculator::NORMAL => 1,
            BebanCalculator::HEAVY => 2,
            BebanCalculator::OVERLOAD => 3,
        ];

        return ($order[$candidate] ?? 0) > ($order[$current] ?? 0) ? $candidate : $current;
    }
}
