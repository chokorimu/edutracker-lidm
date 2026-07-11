<?php

namespace App\Http\Controllers;

use App\Jobs\SendBebanNaikNotifications;
use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Models\NilaiTugas;
use App\Models\NotifikasiDosen;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\UserDosen;
use App\Services\BebanCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenResourceController extends Controller
{
    private const TABS = ['kelas', 'beban', 'notifikasi', 'profil'];

    public function index(Request $request): View
    {
        $user = Auth::guard('dosen')->user();
        $tab = $request->query('tab', 'kelas');

        if (! in_array($tab, self::TABS, true)) {
            $tab = 'kelas';
        }

        $data = [];

        switch ($tab) {
            case 'kelas':
                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)
                    ->withCount('tugas')
                    ->get();
                $data['selectedMkId'] = $request->query('mk');
                $data['submissionMap'] = collect();

                if ($data['selectedMkId']) {
                    $mk = MataKuliah::where('dosen_id', $user->id)
                        ->findOrFail($data['selectedMkId']);
                    $data['selectedMk'] = $mk;
                    $data['tugasList'] = Tugas::where('mata_kuliah_id', $mk->id)
                        ->latest()
                        ->get();
                    $data['siswaList'] = Krs::where('mata_kuliah_id', $mk->id)
                        ->with('siswa')
                        ->get();
                    $tugasIds = $data['tugasList']->pluck('id');
                    $data['nilaiMap'] = NilaiTugas::whereIn('tugas_id', $tugasIds)
                        ->get()
                        ->groupBy('tugas_id')
                        ->map(fn ($grades) => $grades->keyBy('siswa_id'));
                    $data['submissionMap'] = TugasSubmission::whereIn('tugas_id', $tugasIds)
                        ->get()
                        ->groupBy('tugas_id')
                        ->map(fn ($submissions) => $submissions->keyBy('siswa_id'));
                    $data['aggregatePreview'] = Cache::remember(
                        "dosen_preview_{$user->id}",
                        3600,
                        fn () => $this->withPendingSubmissionCounts(
                            BebanCalculator::aggregatePreviewForDosen($user),
                            $user
                        )
                    );
                }
                break;

            case 'beban':
                $weekStart = Carbon::now()->startOfDay();
                $weekEnd = Carbon::now()->addDays(6)->endOfDay();
                $nextWeekStart = Carbon::now()->addDays(7)->startOfDay();
                $nextWeekEnd = Carbon::now()->addDays(13)->endOfDay();

                $data['mataKuliahList'] = MataKuliah::where('dosen_id', $user->id)->with('krs.siswa')->get();
                $data['bimbinganIds'] = DosenPa::where('dosen_id', $user->id)->pluck('siswa_id')->toArray();
                $data['weekStart'] = $weekStart;
                $data['weekEnd'] = $weekEnd;
                $data['nextWeekStart'] = $nextWeekStart;
                $data['nextWeekEnd'] = $nextWeekEnd;
                $data['paRiskCards'] = BebanCalculator::paRiskCards($user);
                $data['selectedBebanMkId'] = $request->query('mk_beban', $data['mataKuliahList']->first()?->id);

                $data['workloadData'] = $data['mataKuliahList']
                    ->filter(fn ($mk) => $mk->id == $data['selectedBebanMkId'])
                    ->map(function ($mk) use ($weekStart, $weekEnd, $nextWeekStart, $nextWeekEnd) {
                        return [
                            'id' => $mk->id,
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
        $this->normalizeDeadlineInput($request);

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date_format:Y-m-d H:i:s|after:now',
            'override' => 'nullable|boolean',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        $mk = MataKuliah::findOrFail($validated['mata_kuliah_id']);
        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak menambah tugas di mata kuliah ini.');
        }

        $tugas = new Tugas;
        $tugas->mata_kuliah_id = $validated['mata_kuliah_id'];
        $tugas->nama = $validated['nama'];
        $tugas->deskripsi = $validated['deskripsi'] ?? null;

        if (! empty($validated['bobot'])) {
            $lockedSum = Tugas::where('mata_kuliah_id', $mk->id)
                ->where('is_bobot_locked', true)
                ->sum('bobot');

            if (($lockedSum + $validated['bobot']) > 100) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'bobot_total' => 'Total bobot terkunci melebihi 100%. Sisa tersedia: '.round(100 - $lockedSum, 2).'%.',
                    ]);
            }

            $tugas->bobot = $validated['bobot'];
            $tugas->is_bobot_locked = true;
        } else {
            $tugas->bobot = 0;
            $tugas->is_bobot_locked = false;
        }

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
        $this->rebalanceBobot($tugas->mata_kuliah_id);
        SendBebanNaikNotifications::dispatch(
            $mk->id,
            $tugas->id,
            $tugas->nama,
            $tugas->deadline,
        );
        Cache::forget("dosen_preview_{$user->id}");
        $this->invalidateSiswaCacheForCourse($mk->id);

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

        return redirect()->route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $tugas->mata_kuliah_id])
            ->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function previewBeban(Request $request): JsonResponse
    {
        $user = Auth::guard('dosen')->user();
        $this->normalizeDeadlineInput($request);

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'deadline' => 'required|date_format:Y-m-d H:i:s|after:now',
        ]);

        $mk = MataKuliah::findOrFail($validated['mata_kuliah_id']);
        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat beban mata kuliah ini.');
        }

        $deadline = Carbon::parse($validated['deadline']);
        $weekStart = $deadline->copy()->startOfDay();
        $weekEnd = $deadline->copy()->addDays(6)->endOfDay();
        $pendingStudentIds = $this->pendingStudentIdsForCourseWeek($mk->id, $weekStart, $weekEnd);
        $rows = BebanCalculator::weeklyLoadForCourse($mk->id, $weekStart, $weekEnd);
        $students = $rows->filter(fn (array $row) => $pendingStudentIds->contains($row['siswa_id']))
            ->map(function (array $row) {
                $projectedCount = ((int) $row['count']) + 1;
                $status = BebanCalculator::forCount($projectedCount);

                return [
                    'siswa_id' => $row['siswa_id'],
                    'nim' => $row['nim'],
                    'nama' => $row['nama_siswa'],
                    'current_count' => (int) $row['count'],
                    'projected_count' => $projectedCount,
                    'status' => $status,
                    'label' => BebanCalculator::label($status),
                    'color' => BebanCalculator::colorClass($status),
                ];
            })->values();

        $worstStatus = $students->pluck('status')
            ->sortByDesc(fn ($status) => BebanCalculator::severity($status))
            ->first() ?? BebanCalculator::LIGHT;

        $needsWarning = in_array($worstStatus, [BebanCalculator::HEAVY, BebanCalculator::OVERLOAD], true);

        $suggestions = $needsWarning
            ? collect(BebanCalculator::rescheduleSuggestions($mk->id, $validated['deadline']))
                ->map(fn (array $suggestion) => array_merge($suggestion, [
                    'label_status' => BebanCalculator::label($suggestion['status']),
                    'color' => BebanCalculator::colorClass($suggestion['status']),
                ]))
                ->values()
                ->toArray()
            : [];

        return response()->json([
            'course' => [
                'id' => $mk->id,
                'nama' => $mk->nama,
                'kode' => $mk->kode,
            ],
            'week' => [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'label' => $weekStart->translatedFormat('d M').' - '.$weekEnd->translatedFormat('d M Y'),
            ],
            'summary' => [
                'students' => $students->count(),
                'avg_tasks' => round($students->avg('projected_count') ?? 0, 1),
                'worst_status' => $worstStatus,
                'label' => BebanCalculator::label($worstStatus),
                'color' => BebanCalculator::colorClass($worstStatus),
                'needs_warning' => $needsWarning,
            ],
            'students' => $students,
            'suggestions' => $suggestions,
        ]);
    }

    public function updateTugas(Request $request, int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $this->normalizeDeadlineInput($request);
        $tugas = Tugas::findOrFail($id);
        $mk = $tugas->mataKuliah;

        if ($mk->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengubah tugas ini.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date_format:Y-m-d H:i:s',
            'status' => 'required|in:aktif,selesai',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        if (! empty($validated['bobot'])) {
            $lockedSum = Tugas::where('mata_kuliah_id', $mk->id)
                ->where('is_bobot_locked', true)
                ->where('id', '!=', $tugas->id)
                ->sum('bobot');

            if (($lockedSum + $validated['bobot']) > 100) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'bobot_total' => 'Total bobot terkunci melebihi 100%. Sisa tersedia: '.round(100 - $lockedSum, 2).'%.',
                    ]);
            }

            $tugas->bobot = $validated['bobot'];
            $tugas->is_bobot_locked = true;
        } else {
            $tugas->is_bobot_locked = false;
        }

        $tugas->fill($validated);
        $tugas->status_beban = $this->computeStatusBeban($mk->id, $validated['deadline'], $tugas->id);
        $tugas->save();
        $this->rebalanceBobot($tugas->mata_kuliah_id);

        return redirect()->route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $tugas->mata_kuliah_id])
            ->with('status', 'Tugas diperbarui.');
    }

    public function destroyTugas(int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $tugas = Tugas::findOrFail($id);

        if ($tugas->mataKuliah->dosen_id !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus tugas ini.');
        }

        $mataKuliahId = $tugas->mata_kuliah_id;
        $tugas->delete();
        $this->rebalanceBobot($mataKuliahId);
        Cache::forget("dosen_preview_{$user->id}");
        $this->invalidateSiswaCacheForCourse($mataKuliahId);

        return redirect()->route('dosen.dashboard', ['tab' => 'kelas', 'mk' => $mataKuliahId])
            ->with('status', 'Tugas dihapus.');
    }

    public function storeNilai(Request $request, int $tugasId, int $siswaId): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $tugas = Tugas::with('mataKuliah')->findOrFail($tugasId);

        if ($tugas->mataKuliah->dosen_id !== $user->id) {
            abort(403);
        }

        $enrolled = Krs::where('mata_kuliah_id', $tugas->mata_kuliah_id)
            ->where('siswa_id', $siswaId)
            ->exists();

        if (! $enrolled) {
            abort(403);
        }

        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'komentar' => 'nullable|string|max:500',
        ]);

        NilaiTugas::updateOrCreate(
            ['tugas_id' => $tugasId, 'siswa_id' => $siswaId],
            ['nilai' => $validated['nilai'], 'komentar' => $validated['komentar'] ?? null]
        );

        $this->recalcNilaiAkhir($tugas->mata_kuliah_id, $siswaId);
        Cache::forget("siswa_dashboard_{$siswaId}");

        return redirect()->route('dosen.dashboard', [
            'tab' => 'kelas',
            'mk' => $tugas->mata_kuliah_id,
        ])->with('status', 'Nilai disimpan.');
    }

    public function downloadSubmission(int $submissionId): StreamedResponse
    {
        $user = Auth::guard('dosen')->user();
        $submission = TugasSubmission::with('tugas.mataKuliah')->findOrFail($submissionId);

        if ($submission->tugas->mataKuliah->dosen_id !== $user->id) {
            abort(403);
        }

        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path, $submission->file_name);
    }

    public function markNotifikasiRead(int $id): RedirectResponse
    {
        $user = Auth::guard('dosen')->user();
        $notif = NotifikasiDosen::where('dosen_id', $user->id)->findOrFail($id);
        $notif->update(['is_read' => true]);

        return redirect()->route('dosen.dashboard', ['tab' => 'notifikasi'])
            ->with('status', 'Notifikasi dibaca.');
    }

    private function withPendingSubmissionCounts(array $previews, UserDosen $dosen): array
    {
        [$weekStart, $weekEnd] = $this->resolveDosenAggregatePreviewWeek($dosen);

        return collect($previews)
            ->map(function (array $preview) use ($weekStart, $weekEnd) {
                $stats = $this->pendingSubmissionStatsForCourseWeek($preview['id'], $weekStart, $weekEnd);

                return array_merge($preview, [
                    'students' => $stats['students'],
                    'avg_tasks' => $stats['avg_tasks'],
                ]);
            })
            ->values()
            ->toArray();
    }

    private function resolveDosenAggregatePreviewWeek(UserDosen $dosen): array
    {
        $now = now();

        return [$now->copy()->startOfDay(), $now->copy()->addDays(6)->endOfDay()];
    }

    private function pendingStudentIdsForCourseWeek(int $mataKuliahId, Carbon $weekStart, Carbon $weekEnd)
    {
        return $this->pendingSubmissionStatsForCourseWeek($mataKuliahId, $weekStart, $weekEnd)['student_ids'];
    }

    private function pendingSubmissionStatsForCourseWeek(int $mataKuliahId, Carbon $weekStart, Carbon $weekEnd): array
    {
        $studentIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        if ($studentIds->isEmpty()) {
            return [
                'student_ids' => collect(),
                'students' => 0,
                'avg_tasks' => 0,
            ];
        }

        $taskIds = Tugas::where('mata_kuliah_id', $mataKuliahId)
            ->whereBetween('deadline', [$weekStart, $weekEnd])
            ->pluck('id');

        if ($taskIds->isEmpty()) {
            return [
                'student_ids' => $studentIds,
                'students' => $studentIds->count(),
                'avg_tasks' => 0,
            ];
        }

        $submittedCounts = TugasSubmission::whereIn('tugas_id', $taskIds)
            ->whereIn('siswa_id', $studentIds)
            ->selectRaw('siswa_id, COUNT(*) as aggregate')
            ->groupBy('siswa_id')
            ->pluck('aggregate', 'siswa_id');

        $pendingCounts = $studentIds
            ->mapWithKeys(fn ($studentId) => [
                $studentId => max(0, $taskIds->count() - (int) $submittedCounts->get($studentId, 0)),
            ])
            ->filter(fn ($count) => $count > 0);

        return [
            'student_ids' => $pendingCounts->keys()->values(),
            'students' => $pendingCounts->count(),
            'avg_tasks' => round($pendingCounts->avg() ?? 0, 1),
        ];
    }

    private function computeStatusBeban(int $mataKuliahId, string $deadline, ?int $excludeTugasId = null): string
    {
        $deadlineDate = Carbon::parse($deadline);
        $weekStart = (clone $deadlineDate)->startOfDay();
        $weekEnd = (clone $deadlineDate)->addDays(6)->endOfDay();

        $siswaIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return BebanCalculator::LIGHT;
        }

        $courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)
            ->get(['siswa_id', 'mata_kuliah_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('mata_kuliah_id'));

        $allCourseIds = $courseIdsBySiswa->flatten()->unique()->values();
        $weeklyTugas = Tugas::whereIn('mata_kuliah_id', $allCourseIds)
            ->whereBetween('deadline', [$weekStart, $weekEnd])
            ->when($excludeTugasId, fn ($q) => $q->where('id', '!=', $excludeTugasId))
            ->get(['id', 'mata_kuliah_id'])
            ->groupBy('mata_kuliah_id');

        $weeklyTugasIds = $weeklyTugas->flatten()->pluck('id');
        $submittedPairs = TugasSubmission::whereIn('tugas_id', $weeklyTugasIds)
            ->whereIn('siswa_id', $siswaIds)
            ->get(['tugas_id', 'siswa_id'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->pluck('tugas_id')->toArray());

        $worst = BebanCalculator::LIGHT;
        $severity = [
            BebanCalculator::LIGHT => 0,
            BebanCalculator::NORMAL => 1,
            BebanCalculator::HEAVY => 2,
            BebanCalculator::OVERLOAD => 3,
        ];

        foreach ($siswaIds as $siswaId) {
            $studentCourseIds = $courseIdsBySiswa->get($siswaId, collect());
            $submittedTugasIds = $submittedPairs->get($siswaId, []);

            $count = $studentCourseIds->sum(function ($courseId) use ($weeklyTugas, $submittedTugasIds) {
                return $weeklyTugas->get($courseId, collect())->pluck('id')->diff($submittedTugasIds)->count();
            }) + 1;

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

    private function invalidateSiswaCacheForCourse(int $mataKuliahId): void
    {
        $siswaIds = Krs::where('mata_kuliah_id', $mataKuliahId)->pluck('siswa_id');

        foreach ($siswaIds as $siswaId) {
            Cache::forget("siswa_dashboard_{$siswaId}");
        }
    }

    private function normalizeDeadlineInput(Request $request): void
    {
        if (! $request->filled('deadline_time') && $request->filled(['deadline_hour', 'deadline_minute'])) {
            $request->merge([
                'deadline_time' => $request->input('deadline_hour').':'.$request->input('deadline_minute'),
            ]);
        }

        if (! $request->filled('deadline') && $request->filled(['deadline_date', 'deadline_time'])) {
            $request->merge([
                'deadline' => $request->input('deadline_date').' '.$request->input('deadline_time').':00',
            ]);
        }

        if (! $request->filled('deadline')) {
            return;
        }

        try {
            $request->merge([
                'deadline' => Carbon::parse($request->input('deadline'))->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Leave invalid input untouched so validation returns the field error.
        }
    }

    private function rebalanceBobot(int $mataKuliahId): void
    {
        $tugas = Tugas::where('mata_kuliah_id', $mataKuliahId)->get();
        if ($tugas->isEmpty()) {
            return;
        }

        $locked = $tugas->where('is_bobot_locked', true);
        $auto = $tugas->where('is_bobot_locked', false);
        $totalLocked = $locked->sum('bobot');
        $sisaBobot = max(0, 100 - $totalLocked);
        $autoCount = $auto->count();

        if ($autoCount === 0) {
            return;
        }

        $bobotPerAuto = round($sisaBobot / $autoCount, 4);
        $autoArr = $auto->values();

        foreach ($autoArr as $i => $t) {
            $t->bobot = ($i === $autoCount - 1)
                ? round($sisaBobot - ($bobotPerAuto * ($autoCount - 1)), 4)
                : $bobotPerAuto;
            $t->saveQuietly();
        }
    }

    private function recalcNilaiAkhir(int $mataKuliahId, int $siswaId): void
    {
        $tugasList = Tugas::where('mata_kuliah_id', $mataKuliahId)->get();

        if ($tugasList->isEmpty()) {
            return;
        }

        $nilaiList = NilaiTugas::whereIn('tugas_id', $tugasList->pluck('id'))
            ->where('siswa_id', $siswaId)
            ->get()
            ->keyBy('tugas_id');

        $totalBobot = 0;
        $weightedSum = 0;

        foreach ($tugasList as $tugas) {
            if (isset($nilaiList[$tugas->id])) {
                // BUG-4 fix: accumulate nilai * bobot (not bobot/100) so division
                // by totalBobot gives the correct weighted average over graded tasks only.
                // Old: sum(nilai * bobot/100) / totalBobot * 100  → inflated when partial
                // New: sum(nilai * bobot) / sum(graded bobot)      → always correct
                $weightedSum += $nilaiList[$tugas->id]->nilai * $tugas->bobot;
                $totalBobot += $tugas->bobot;
            }
        }

        if ($totalBobot > 0) {
            $nilaiAkhir = round($weightedSum / $totalBobot, 2);
            Krs::where('mata_kuliah_id', $mataKuliahId)
                ->where('siswa_id', $siswaId)
                ->update([
                    'nilai_akhir' => $nilaiAkhir,
                    'nilai_huruf' => $this->toHuruf($nilaiAkhir),
                ]);
        }
    }

    private function toHuruf(float $nilai): string
    {
        return match (true) {
            $nilai >= 85 => 'A',
            $nilai >= 80 => 'A-',
            $nilai >= 75 => 'B+',
            $nilai >= 70 => 'B',
            $nilai >= 65 => 'B-',
            $nilai >= 60 => 'C+',
            $nilai >= 55 => 'C',
            $nilai >= 50 => 'D',
            default => 'E',
        };
    }
}
