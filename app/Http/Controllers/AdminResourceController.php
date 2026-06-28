<?php

namespace App\Http\Controllers;

use App\Models\DosenPa;
use App\Models\IpkHistory;
use App\Models\KalenderAkademik;
use App\Models\Krs;
use App\Models\Laporan;
use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\NotifikasiDosen;
use App\Models\Pengaturan;
use App\Models\Tugas;
use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminResourceController extends Controller
{
    public function index(Request $request): View
    {
        $resources = $this->resources();
        $resourceKey = $request->query('resource', 'siswas');

        if (! isset($resources[$resourceKey])) {
            $resourceKey = 'siswas';
        }

        $config = $resources[$resourceKey];
        $modelClass = $config['model'];
        $editId = $request->query('edit');
        $editing = $editId ? $modelClass::find($editId) : null;
        $records = $modelClass::latest('id')->paginate(10)->withQueryString();
        $krsGroups = null;

        if ($resourceKey === 'krs') {
            $records = Krs::with(['siswa', 'mataKuliah'])->latest('id')->paginate(10)->withQueryString();
            $krsGroups = UserSiswa::whereHas('krs')
                ->with(['krs' => fn ($query) => $query->with('mataKuliah')->orderBy('semester')->orderBy('tahun_ajaran')->orderBy('id')])
                ->orderBy('name')
                ->paginate(10, ['*'], 'students_page')
                ->withQueryString();
        }

        return view('pages.admin.⚡dashboard', [
            'user' => Auth::guard('admin')->user(),
            'resources' => $resources,
            'resourceKey' => $resourceKey,
            'config' => $config,
            'records' => $records,
            'editing' => $editing,
            'counts' => $this->counts($resources),
            'options' => $this->formOptions(),
            'krsGroups' => $krsGroups,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->resource($resource);

        if ($resource === 'krs' && $request->input('_mode') === 'batch') {
            return $this->storeKrsBatch($request, $config);
        }

        $data = $this->validatedData($request, $resource, $config);

        $config['model']::create($data);

        return redirect()
            ->route('admin.dashboard', ['resource' => $resource])
            ->with('status', "{$config['label']} berhasil dibuat.");
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->resource($resource);
        $record = $config['model']::findOrFail($id);
        $data = $this->validatedData($request, $resource, $config, $record);

        $record->update($data);

        return redirect()
            ->route('admin.dashboard', ['resource' => $resource])
            ->with('status', "{$config['label']} berhasil diperbarui.");
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = $this->resource($resource);
        $record = $config['model']::findOrFail($id);

        $record->delete();

        return redirect()
            ->route('admin.dashboard', ['resource' => $resource])
            ->with('status', "{$config['label']} berhasil dihapus.");
    }

    private function resource(string $resource): array
    {
        abort_unless(isset($this->resources()[$resource]), 404);

        return $this->resources()[$resource];
    }

    private function resources(): array
    {
        return [
            'admins' => [
                'label' => 'Admin',
                'model' => UserAdmin::class,
                'fields' => [
                    'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                    'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
                    'password' => ['label' => 'Password', 'type' => 'password', 'required' => true, 'hide_table' => true],
                ],
            ],
            'dosens' => [
                'label' => 'Dosen',
                'model' => UserDosen::class,
                'fields' => [
                    'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                    'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
                    'password' => ['label' => 'Password', 'type' => 'password', 'required' => true, 'hide_table' => true],
                    'nidn' => ['label' => 'NIDN', 'type' => 'text', 'required' => true],
                    'fakultas' => ['label' => 'Fakultas', 'type' => 'text'],
                ],
            ],
            'siswas' => [
                'label' => 'Siswa',
                'model' => UserSiswa::class,
                'fields' => [
                    'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                    'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
                    'password' => ['label' => 'Password', 'type' => 'password', 'required' => true, 'hide_table' => true],
                    'nim' => ['label' => 'NIM', 'type' => 'text', 'required' => true],
                    'prodi' => ['label' => 'Prodi', 'type' => 'text'],
                    'semester' => ['label' => 'Semester', 'type' => 'number', 'min' => 1, 'max' => 14],
                ],
            ],
            'mata-kuliah' => [
                'label' => 'Mata Kuliah',
                'model' => MataKuliah::class,
                'fields' => [
                    'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                    'kode' => ['label' => 'Kode', 'type' => 'text', 'required' => true],
                    'sks' => ['label' => 'SKS', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 6],
                    'dosen_id' => ['label' => 'Dosen', 'type' => 'select', 'required' => true, 'options' => 'dosens'],
                    'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'required' => true],
                    'semester' => ['label' => 'Semester', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 14],
                ],
            ],
            'dosen-pa' => [
                'label' => 'Dosen PA',
                'model' => DosenPa::class,
                'fields' => [
                    'dosen_id' => ['label' => 'Dosen', 'type' => 'select', 'required' => true, 'options' => 'dosens'],
                    'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'required' => true, 'options' => 'siswas'],
                    'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'required' => true],
                ],
            ],
            'ipk-history' => [
                'label' => 'IPK History',
                'model' => IpkHistory::class,
                'fields' => [
                    'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'required' => true, 'options' => 'siswas'],
                    'ipk' => ['label' => 'IPK', 'type' => 'number', 'required' => true, 'step' => '0.01', 'min' => 0, 'max' => 4],
                    'semester' => ['label' => 'Semester', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 14],
                    'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'required' => true],
                    'total_sks' => ['label' => 'Total SKS', 'type' => 'number', 'required' => true, 'min' => 0],
                    'rekomendasi_sks' => ['label' => 'Rekomendasi SKS', 'type' => 'number', 'min' => 0],
                ],
            ],
            'kalender-akademik' => [
                'label' => 'Kalender Akademik',
                'model' => KalenderAkademik::class,
                'fields' => [
                    'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                    'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                    'tipe' => ['label' => 'Tipe', 'type' => 'text', 'required' => true],
                    'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'required' => true],
                    'created_by' => ['label' => 'Admin', 'type' => 'select', 'required' => true, 'options' => 'admins'],
                ],
            ],
            'krs' => [
                'label' => 'KRS',
                'model' => Krs::class,
                'fields' => [
                    'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'required' => true, 'options' => 'siswas'],
                    'mata_kuliah_id' => ['label' => 'Mata Kuliah', 'type' => 'select', 'required' => true, 'options' => 'mata_kuliah'],
                    'semester' => ['label' => 'Semester', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 14],
                    'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'required' => true],
                    'nilai_akhir' => ['label' => 'Nilai Akhir', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'max' => 100],
                    'nilai_huruf' => ['label' => 'Nilai Huruf', 'type' => 'text'],
                    'status' => ['label' => 'Status', 'type' => 'text', 'required' => true],
                ],
            ],
            'notifikasi' => [
                'label' => 'Notifikasi Siswa',
                'model' => Notifikasi::class,
                'fields' => [
                    'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'required' => true, 'options' => 'siswas'],
                    'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                    'pesan' => ['label' => 'Pesan', 'type' => 'textarea', 'required' => true],
                    'tipe' => ['label' => 'Tipe', 'type' => 'text', 'required' => true],
                    'sumber' => ['label' => 'Sumber', 'type' => 'text'],
                    'is_read' => ['label' => 'Sudah Dibaca', 'type' => 'checkbox'],
                ],
            ],
            'notifikasi-dosen' => [
                'label' => 'Notifikasi Dosen',
                'model' => NotifikasiDosen::class,
                'fields' => [
                    'dosen_id' => ['label' => 'Dosen', 'type' => 'select', 'required' => true, 'options' => 'dosens'],
                    'mata_kuliah_id' => ['label' => 'Mata Kuliah', 'type' => 'select', 'required' => true, 'options' => 'mata_kuliah'],
                    'tugas_id' => ['label' => 'Tugas', 'type' => 'select', 'options' => 'tugas'],
                    'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                    'pesan' => ['label' => 'Pesan', 'type' => 'textarea', 'required' => true],
                    'tipe' => ['label' => 'Tipe', 'type' => 'text', 'required' => true],
                    'sumber' => ['label' => 'Sumber', 'type' => 'text'],
                    'is_read' => ['label' => 'Sudah Dibaca', 'type' => 'checkbox'],
                ],
            ],
            'laporan' => [
                'label' => 'Laporan',
                'model' => Laporan::class,
                'fields' => [
                    'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                    'tipe' => ['label' => 'Tipe', 'type' => 'text', 'required' => true],
                    'periode' => ['label' => 'Periode', 'type' => 'text', 'required' => true],
                    'file_path' => ['label' => 'File Path', 'type' => 'text', 'required' => true],
                    'created_by' => ['label' => 'Admin', 'type' => 'select', 'required' => true, 'options' => 'admins'],
                ],
            ],
            'pengaturan' => [
                'label' => 'Pengaturan',
                'model' => Pengaturan::class,
                'fields' => [
                    'setting_key' => ['label' => 'Key', 'type' => 'text', 'required' => true],
                    'value' => ['label' => 'Value', 'type' => 'text'],
                    'updated_by' => ['label' => 'Admin', 'type' => 'select', 'options' => 'admins'],
                ],
            ],
        ];
    }

    private function validatedData(Request $request, string $resource, array $config, ?Model $record = null): array
    {
        $rules = [];

        foreach ($config['fields'] as $field => $fieldConfig) {
            $rules[$field] = $this->rulesForField($resource, $field, $fieldConfig, $record);
        }

        $data = $request->validate($rules);

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (($fieldConfig['type'] ?? null) === 'checkbox') {
                $data[$field] = $request->boolean($field);
            }

            if (($fieldConfig['type'] ?? null) === 'password') {
                if (blank($request->input($field))) {
                    unset($data[$field]);

                    continue;
                }

                $data[$field] = Hash::make($request->input($field));
            }
        }

        return $data;
    }

    private function rulesForField(string $resource, string $field, array $fieldConfig, ?Model $record): array
    {
        $rules = [];
        $required = ($fieldConfig['required'] ?? false) && ! ($record && ($fieldConfig['type'] ?? null) === 'password');
        $rules[] = $required ? 'required' : 'nullable';

        match ($fieldConfig['type'] ?? 'text') {
            'email' => $rules[] = 'email',
            'number' => $rules[] = 'numeric',
            'select' => $rules[] = 'integer',
            'date' => $rules[] = 'date',
            'checkbox' => $rules[] = 'boolean',
            default => $rules[] = 'string',
        };

        if (isset($fieldConfig['min'])) {
            $rules[] = 'min:'.$fieldConfig['min'];
        }

        if (isset($fieldConfig['max'])) {
            $rules[] = 'max:'.$fieldConfig['max'];
        }

        if ($field === 'email') {
            $rules[] = Rule::unique((new ($this->resource($resource)['model']))->getTable(), 'email')->ignore($record?->getKey());
        }

        if ($field === 'nim') {
            $rules[] = Rule::unique('user_siswa', 'nim')->ignore($record?->getKey());
        }

        if ($field === 'nidn') {
            $rules[] = Rule::unique('user_dosens', 'nidn')->ignore($record?->getKey());
        }

        if ($field === 'kode') {
            $rules[] = Rule::unique('mata_kuliah', 'kode')->ignore($record?->getKey());
        }

        if ($field === 'setting_key') {
            $rules[] = Rule::unique('pengaturan', 'setting_key')->ignore($record?->getKey());
        }

        if (str_ends_with($field, '_id') || in_array($field, ['created_by', 'updated_by'], true)) {
            $table = $this->foreignTable($field);
            $rules[] = Rule::exists($table, 'id');
        }

        return $rules;
    }

    private function foreignTable(string $field): string
    {
        return match ($field) {
            'dosen_id' => 'user_dosens',
            'siswa_id' => 'user_siswa',
            'mata_kuliah_id' => 'mata_kuliah',
            'tugas_id' => 'tugas',
            'created_by', 'updated_by' => 'user_admin',
            default => $field,
        };
    }

    private function formOptions(): array
    {
        $mataKuliah = MataKuliah::orderBy('tahun_ajaran')
            ->orderBy('semester')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'semester', 'tahun_ajaran']);

        return [
            'admins' => UserAdmin::orderBy('name')->get(['id', 'name', 'email']),
            'dosens' => UserDosen::orderBy('name')->get(['id', 'name', 'email']),
            'siswas' => UserSiswa::orderBy('name')->get(['id', 'name', 'email']),
            'mata_kuliah' => $mataKuliah,
            'krs_packages' => $mataKuliah
                ->groupBy(fn (MataKuliah $mataKuliah) => $mataKuliah->tahun_ajaran.'|'.$mataKuliah->semester)
                ->map(function ($items, string $key) {
                    [$tahunAjaran, $semester] = explode('|', $key, 2);

                    return [
                        'key' => $key,
                        'label' => "Semester {$semester} - {$tahunAjaran}",
                        'semester' => (int) $semester,
                        'tahun_ajaran' => $tahunAjaran,
                        'mata_kuliah_ids' => $items->pluck('id')->values(),
                        'total' => $items->count(),
                    ];
                })
                ->values(),
            'tugas' => Tugas::orderBy('nama')->get(['id', 'nama']),
        ];
    }

    private function storeKrsBatch(Request $request, array $config): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id' => ['required', 'integer', Rule::exists('user_siswa', 'id')],
            'krs_package' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        [$tahunAjaran, $semester] = array_pad(explode('|', $validated['krs_package'], 2), 2, null);

        if (! $tahunAjaran || ! $semester || ! is_numeric($semester)) {
            return back()
                ->withErrors(['krs_package' => 'Paket KRS tidak valid.'])
                ->withInput();
        }

        $mataKuliahIds = MataKuliah::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', (int) $semester)
            ->pluck('id');

        if ($mataKuliahIds->isEmpty()) {
            return back()
                ->withErrors(['krs_package' => 'Paket KRS belum memiliki mata kuliah.'])
                ->withInput();
        }

        $created = 0;
        $skipped = 0;

        foreach ($mataKuliahIds as $mataKuliahId) {
            $krs = Krs::firstOrCreate(
                [
                    'siswa_id' => $validated['siswa_id'],
                    'mata_kuliah_id' => $mataKuliahId,
                    'semester' => (int) $semester,
                    'tahun_ajaran' => $tahunAjaran,
                ],
                ['status' => $validated['status']]
            );

            $krs->wasRecentlyCreated ? $created++ : $skipped++;
        }

        return redirect()
            ->route('admin.dashboard', ['resource' => 'krs'])
            ->with('status', "{$config['label']} paket berhasil diproses: {$created} dibuat, {$skipped} sudah ada.");
    }

    private function counts(array $resources): array
    {
        $counts = [];

        foreach ($resources as $key => $resource) {
            $counts[$key] = $resource['model']::count();
        }

        return $counts;
    }

    public function laporanIndex(Request $request): View
    {
        $resources = $this->resources();
        $laporans = Laporan::latest('id')->paginate(10)->withQueryString();
        $prodis = UserSiswa::distinct()->pluck('prodi')->filter()->values();

        return view('pages.admin.⚡dashboard', [
            'user' => Auth::guard('admin')->user(),
            'resources' => $resources,
            'resourceKey' => 'laporan',
            'config' => $resources['laporan'],
            'records' => $laporans,
            'counts' => $this->counts($resources),
            'options' => $this->formOptions(),
            'laporanProdis' => $prodis,
        ]);
    }

    public function laporanGenerate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prodi' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tipe' => 'required|string',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $prodiFilter = $validated['prodi'] ?? null;
        $tipe = $validated['tipe'];

        $query = UserSiswa::query();
        if ($prodiFilter) {
            $query->where('prodi', $prodiFilter);
        }
        $students = $query->get();

        $totalStudents = $students->count();
        $avgIpk = $students->map(fn ($s) => $s->ipkHistory()->latest('semester')->first()?->ipk ?? 0)->avg() ?? 0;
        $avgSks = $students->map(fn ($s) => $s->krs()->whereColumn('semester', 'user_siswa.semester')->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')->sum('mata_kuliah.sks'))->avg() ?? 0;

        $overloadCount = 0;
        $collisionCount = 0;

        foreach ($students as $student) {
            $sks = $student->krs()->whereColumn('semester', 'user_siswa.semester')
                ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
                ->sum('mata_kuliah.sks');
            if ($sks > 24) {
                $overloadCount++;
            }

            $tasks = $student->krs()
                ->whereColumn('semester', 'user_siswa.semester')
                ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
                ->join('tugas', 'tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                ->whereBetween('tugas.deadline', [$startDate, $endDate])
                ->count();
            if ($tasks >= 3) {
                $collisionCount++;
            }
        }

        $htmlContent = "<h1>Laporan Akademik</h1><p>Tipe: {$tipe}</p><p>Periode: {$startDate} - {$endDate}</p><p>Total Mahasiswa: {$totalStudents}</p><p>Rata-rata IPK: ".number_format($avgIpk, 2).'</p><p>Rata-rata SKS: '.number_format($avgSks, 1)."</p><p>Overload (>24 SKS): {$overloadCount}</p><p>Deadline Padat (>3 tugas/minggu): {$collisionCount}</p>";

        $fileName = 'laporan_'.$tipe.'_'.date('YmdHis').'.html';
        $filePath = 'laporans/'.$fileName;
        \Storage::disk('public')->put($filePath, $htmlContent);

        Laporan::create([
            'judul' => 'Laporan '.$tipe.' - '.$startDate.' s.d. '.$endDate,
            'tipe' => $tipe,
            'periode' => $startDate.' - '.$endDate.($prodiFilter ? ' ('.$prodiFilter.')' : ''),
            'file_path' => $filePath,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.laporan.index')->with('status', 'Laporan berhasil dibuat.');
    }
}
