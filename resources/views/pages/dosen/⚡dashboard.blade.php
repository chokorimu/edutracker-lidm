@php use App\Services\BebanCalculator; @endphp
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Dosen</h1>
                <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ $user->name }}</p>
            </div>
            <form method="POST" action="{{ route('dosen.logout') }}">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm">Logout</button>
            </form>
        </div>

        {{-- Flash status --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-md mb-4 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Tab Navigation --}}
        <div class="flex space-x-1 border-b border-gray-200 mb-6">
            @php $tabs = ['tugas' => 'Tugas', 'beban' => 'Beban', 'notifikasi' => 'Notifikasi', 'profil' => 'Profil']; @endphp
            @foreach ($tabs as $key => $label)
                <a href="{{ route('dosen.dashboard', ['tab' => $key]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-t-md transition {{ $currentTab === $key ? 'bg-white border border-b-0 border-gray-200 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Tab Content --}}

        {{-- ===== TUGAS ===== --}}
        @if ($currentTab === 'tugas')
            <div class="space-y-6">
                {{-- Create Form --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Tambah Tugas Baru</h2>

                    @if ($errors->has('beban_warning'))
                        <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-md mb-4 text-sm">
                            {{ $errors->first('beban_warning') }}
                            @if(session('deadline_suggestions'))
                                <div class="mt-3">
                                    <p class="font-semibold mb-2">Saran jadwal yang lebih aman:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(session('deadline_suggestions') as $suggestion)
                                            <button type="button"
                                                    data-deadline-suggestion="{{ $suggestion['value'] }}"
                                                    class="px-3 py-1 rounded-full bg-white border border-yellow-300 text-yellow-900 text-xs hover:bg-yellow-50">
                                                {{ $suggestion['label'] }} · {{ $suggestion['count'] }} tugas
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(count($data['aggregatePreview']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                            @foreach($data['aggregatePreview'] as $preview)
                                <div class="border rounded-lg p-3 {{ $preview['color'] }}">
                                    <p class="text-xs font-semibold">{{ $preview['nama'] }} ({{ $preview['kode'] }})</p>
                                    <p class="text-[11px] mt-1">{{ $preview['students'] }} mahasiswa · rata-rata {{ $preview['avg_tasks'] }} tugas minggu ini</p>
                                    <p class="text-[11px] font-bold mt-1">Status terberat: {{ $preview['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dosen.tugas.store') }}" class="space-y-4" data-preview-form data-preview-url="{{ route('dosen.tugas.preview-beban') }}">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                            <select name="mata_kuliah_id" required data-preview-course
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($data['mataKuliahList'] as $mk)
                                    <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                        {{ $mk->nama }} ({{ $mk->kode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Tugas</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="255"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" rows="3"
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bobot (%)</label>
                                <input type="number" name="bobot" value="{{ old('bobot') }}" min="0" max="100"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deadline</label>
                                <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline') }}" required data-preview-deadline
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="hidden rounded-lg border border-gray-200 bg-gray-50 p-4" data-preview-panel>
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Preview Beban Mahasiswa</h3>
                                    <p class="text-xs text-gray-500 mt-1" data-preview-week>Isi mata kuliah dan deadline untuk melihat estimasi.</p>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-semibold" data-preview-status></span>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="rounded-md bg-white border border-gray-200 p-3">
                                    <p class="text-[11px] font-semibold uppercase text-gray-500">Mahasiswa terdampak</p>
                                    <p class="text-xl font-bold text-gray-900 mt-1" data-preview-students>0</p>
                                </div>
                                <div class="rounded-md bg-white border border-gray-200 p-3">
                                    <p class="text-[11px] font-semibold uppercase text-gray-500">Rata-rata tugas</p>
                                    <p class="text-xl font-bold text-gray-900 mt-1" data-preview-average>0</p>
                                </div>
                                <div class="rounded-md bg-white border border-gray-200 p-3">
                                    <p class="text-[11px] font-semibold uppercase text-gray-500">Status terberat</p>
                                    <p class="text-xl font-bold text-gray-900 mt-1" data-preview-label>-</p>
                                </div>
                            </div>

                            <div class="hidden mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800" data-preview-warning>
                                <p class="font-semibold">Peringatan: pekan deadline ini sudah padat.</p>
                                <p class="text-xs mt-1">Pilih salah satu saran deadline di bawah atau centang override untuk tetap menyimpan.</p>
                            </div>

                            <div class="hidden mt-4" data-preview-suggestions-wrap>
                                <p class="text-xs font-semibold text-gray-700 mb-2">Saran reschedule</p>
                                <div class="flex flex-wrap gap-2" data-preview-suggestions></div>
                            </div>

                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left text-gray-500">
                                            <th class="py-2 pr-2">Mahasiswa</th>
                                            <th class="py-2 px-2">Saat ini</th>
                                            <th class="py-2 px-2">Jika disimpan</th>
                                            <th class="py-2 pl-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody data-preview-student-rows></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="override" value="1" id="override" {{ old('override') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="override" class="text-sm text-gray-600">Tetap simpan walau beban berat/overload</label>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
                                Simpan Tugas
                            </button>
                        </div>
                    </form>
                    <script>
                        (() => {
                            const form = document.querySelector("[data-preview-form]");
                            const deadlineInput = document.getElementById("deadline");

                            document.querySelectorAll("[data-deadline-suggestion]").forEach((button) => {
                                button.addEventListener("click", () => {
                                    if (deadlineInput) {
                                        deadlineInput.value = button.dataset.deadlineSuggestion;
                                        deadlineInput.dispatchEvent(new Event("input", { bubbles: true }));
                                    }
                                });
                            });

                            if (!form) {
                                return;
                            }

                            const courseInput = form.querySelector("[data-preview-course]");
                            const previewDeadline = form.querySelector("[data-preview-deadline]");
                            const panel = form.querySelector("[data-preview-panel]");
                            const statusBadge = form.querySelector("[data-preview-status]");
                            const weekLabel = form.querySelector("[data-preview-week]");
                            const studentsCount = form.querySelector("[data-preview-students]");
                            const averageCount = form.querySelector("[data-preview-average]");
                            const summaryLabel = form.querySelector("[data-preview-label]");
                            const warning = form.querySelector("[data-preview-warning]");
                            const suggestionsWrap = form.querySelector("[data-preview-suggestions-wrap]");
                            const suggestions = form.querySelector("[data-preview-suggestions]");
                            const studentRows = form.querySelector("[data-preview-student-rows]");
                            const token = form.querySelector('input[name="_token"]')?.value;
                            let abortController = null;
                            let debounce = null;

                            const setBadgeClass = (element, color) => {
                                element.className = `inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-semibold ${color}`;
                            };

                            const renderSuggestions = (items) => {
                                suggestions.innerHTML = "";
                                suggestionsWrap.classList.toggle("hidden", items.length === 0);

                                items.forEach((item) => {
                                    const button = document.createElement("button");
                                    button.type = "button";
                                    button.className = `rounded-full border px-3 py-1 text-xs font-semibold hover:opacity-80 ${item.color}`;
                                    button.textContent = `${item.label} · ${item.count} tugas · ${item.label_status}`;
                                    button.addEventListener("click", () => {
                                        previewDeadline.value = item.value;
                                        previewDeadline.dispatchEvent(new Event("input", { bubbles: true }));
                                    });
                                    suggestions.appendChild(button);
                                });
                            };

                            const renderStudents = (items) => {
                                studentRows.innerHTML = "";

                                if (items.length === 0) {
                                    const row = document.createElement("tr");
                                    const cell = document.createElement("td");
                                    cell.colSpan = 4;
                                    cell.className = "py-3 text-center text-gray-500";
                                    cell.textContent = "Belum ada mahasiswa KRS pada mata kuliah ini.";
                                    row.appendChild(cell);
                                    studentRows.appendChild(row);
                                    return;
                                }

                                items.forEach((student) => {
                                    const row = document.createElement("tr");
                                    row.className = "border-b border-gray-100 last:border-0";

                                    const identity = document.createElement("td");
                                    identity.className = "py-2 pr-2";
                                    const name = document.createElement("span");
                                    name.className = "font-medium text-gray-900";
                                    name.textContent = student.nama;
                                    const nim = document.createElement("span");
                                    nim.className = "block text-[11px] text-gray-500";
                                    nim.textContent = student.nim;
                                    identity.append(name, nim);

                                    const current = document.createElement("td");
                                    current.className = "py-2 px-2 text-gray-600";
                                    current.textContent = `${student.current_count} tugas`;

                                    const projected = document.createElement("td");
                                    projected.className = "py-2 px-2 font-semibold text-gray-900";
                                    projected.textContent = `${student.projected_count} tugas`;

                                    const status = document.createElement("td");
                                    status.className = "py-2 pl-2";
                                    const badge = document.createElement("span");
                                    badge.className = `inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold ${student.color}`;
                                    badge.textContent = student.label;
                                    status.appendChild(badge);

                                    row.append(identity, current, projected, status);
                                    studentRows.appendChild(row);
                                });
                            };

                            const renderPreview = (payload) => {
                                panel.classList.remove("hidden");
                                weekLabel.textContent = `${payload.course.nama} (${payload.course.kode}) · ${payload.week.label}`;
                                studentsCount.textContent = payload.summary.students;
                                averageCount.textContent = payload.summary.avg_tasks;
                                summaryLabel.textContent = payload.summary.label;
                                statusBadge.textContent = payload.summary.label;
                                setBadgeClass(statusBadge, payload.summary.color);
                                warning.classList.toggle("hidden", !payload.summary.needs_warning);
                                renderSuggestions(payload.suggestions || []);
                                renderStudents(payload.students || []);
                            };

                            const fetchPreview = () => {
                                const mataKuliahId = courseInput.value;
                                const deadline = previewDeadline.value;

                                if (!mataKuliahId || !deadline) {
                                    panel.classList.add("hidden");
                                    return;
                                }

                                abortController?.abort();
                                abortController = new AbortController();

                                fetch(form.dataset.previewUrl, {
                                    method: "POST",
                                    headers: {
                                        "Accept": "application/json",
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": token,
                                    },
                                    body: JSON.stringify({ mata_kuliah_id: mataKuliahId, deadline }),
                                    signal: abortController.signal,
                                })
                                    .then((response) => response.ok ? response.json() : Promise.reject(response))
                                    .then(renderPreview)
                                    .catch((error) => {
                                        if (error.name !== "AbortError") {
                                            panel.classList.add("hidden");
                                        }
                                    });
                            };

                            const schedulePreview = () => {
                                clearTimeout(debounce);
                                debounce = setTimeout(fetchPreview, 250);
                            };

                            courseInput.addEventListener("change", schedulePreview);
                            previewDeadline.addEventListener("input", schedulePreview);
                            schedulePreview();
                        })();
                    </script>
                </div>

                {{-- Tasks List --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Daftar Tugas</h2>
                    @if($data['tugasList']->count())
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-left">
                                    <th class="py-2 px-2">Nama</th>
                                    <th class="py-2 px-2">Mata Kuliah</th>
                                    <th class="py-2 px-2">Deadline</th>
                                    <th class="py-2 px-2">Status Beban</th>
                                    <th class="py-2 px-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['tugasList'] as $tugas)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 px-2">{{ $tugas->nama }}</td>
                                        <td class="py-2 px-2">{{ $tugas->mataKuliah?->nama ?? '-' }}</td>
                                        <td class="py-2 px-2">{{ \Carbon\Carbon::parse($tugas->deadline)->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 px-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $tugas->status_beban === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $tugas->status_beban === BebanCalculator::OVERLOAD ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ $tugas->status_beban }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2">
                                            <form method="POST" action="{{ route('dosen.tugas.destroy', $tugas->id) }}" class="inline"
                                                  onsubmit="return confirm('Hapus tugas ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $data['tugasList']->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada tugas.</p>
                    @endif
                </div>
            </div>

        {{-- ===== BEBAN ===== --}}
        @elseif ($currentTab === 'beban')
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Beban Tugas Mahasiswa</h2>
                    <p class="text-xs text-gray-400 mb-4">Minggu ini: {{ $data['weekStart']->format('d/m/Y') }} – {{ $data['weekEnd']->format('d/m/Y') }}</p>

                    @if(count($data['paRiskCards']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                            @foreach($data['paRiskCards'] as $student)
                                <div class="border rounded-lg p-4 {{ $student['color'] }}">
                                    <div class="flex justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-bold">{{ $student['nama'] }}</h3>
                                            <p class="text-[11px] opacity-80">{{ $student['nim'] }}</p>
                                        </div>
                                        <span class="text-lg font-bold">{{ $student['risk_score'] }}%</span>
                                    </div>
                                    <p class="text-xs mt-2">{{ $student['task_count'] }} tugas minggu ini · {{ $student['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @forelse($data['workloadData'] as $mk)
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <h3 class="font-medium text-sm mb-2">{{ $mk['nama'] }} ({{ $mk['kode'] }})</h3>

                            @php
                                // Merge this‑week and next‑week results from the controller.
                                $combined = collect($mk['thisWeek'])->map(function ($s) use ($mk, $data) {
                                    $next = collect($mk['nextWeek'])->firstWhere('siswa_id', $s['siswa_id']);
                                    return [
                                        'nama' => $s['nama_siswa'],
                                        'siswa_id' => $s['siswa_id'],
                                        'nim' => $s['nim'],
                                        'weekCount' => $s['count'],
                                        'weekLoad' => $s['status'],
                                        'nextWeekCount' => $next['count'] ?? 0,
                                        'nextWeekLoad' => $next['status'] ?? \App\Services\BebanCalculator::LIGHT,
                                        'isBimbingan' => in_array($s['siswa_id'], $data['bimbinganIds']),
                                    ];
                                });
                                $students = $combined;
                            @endphp

                            @if($students->count())
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left">
                                            <th class="py-1 px-1">Nama</th>
                                            <th class="py-1 px-1">NIM</th>
                                            <th class="py-1 px-1">Minggu Ini</th>
                                            <th class="py-1 px-1">Minggu Depan</th>
                                            <th class="py-1 px-1">Bimbingan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    @foreach($students as $s)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-1 px-1">{{ $s['nama'] }}</td>
                                                <td class="py-1 px-1">{{ $s['nim'] ?? $s['siswa_id'] }}</td>
                                                <td class="py-1 px-1">
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-medium
                                                        {{ $s['weekLoad'] === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                        {{ $s['weekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-200 text-red-800' : '' }}">
                                                        {{ $s['weekCount'] }} ({{ $s['weekLoad'] }})
                                                    </span>
                                                </td>
                                                <td class="py-1 px-1">
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-medium
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::LIGHT ? 'bg-green-100 text-green-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::NORMAL ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::HEAVY ? 'bg-orange-100 text-orange-700' : '' }}
                                                        {{ $s['nextWeekLoad'] === BebanCalculator::OVERLOAD ? 'bg-red-200 text-red-800' : '' }}">
                                                        {{ $s['nextWeekCount'] }} ({{ $s['nextWeekLoad'] }})
                                                    </span>
                                                </td>
                                                <td class="py-1 px-1">
                                                    @if($s['isBimbingan'])
                                                        <span class="text-blue-600 font-bold">✓</span>
                                                    @else
                                                        <span class="text-gray-300">–</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-400 text-xs">Tidak ada mahasiswa terdaftar.</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Anda belum mengajar mata kuliah apapun.</p>
                    @endforelse
                </div>
            </div>

        {{-- ===== NOTIFIKASI ===== --}}
        @elseif ($currentTab === 'notifikasi')
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Notifikasi</h2>

                    @if($data['notifikasiList']->count())
                        @foreach($data['notifikasiList'] as $notif)
                            <div class="border border-gray-200 rounded-lg p-4 mb-3 {{ $notif->is_read ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-sm">{{ $notif->judul }}</h4>
                                        <p class="text-xs text-gray-600 mt-1">{{ $notif->pesan }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                        @if($notif->tipe === 'beban_tinggi')
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Beban Tinggi</span>
                                        @endif
                                    </div>
                                    @if(!$notif->is_read)
                                        <form method="POST" action="{{ route('dosen.notifikasi.read', $notif->id) }}" class="ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium whitespace-nowrap">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-4">
                            {{ $data['notifikasiList']->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada notifikasi.</p>
                    @endif
                </div>
            </div>

        {{-- ===== PROFIL ===== --}}
        @elseif ($currentTab === 'profil')
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Profil Dosen</h2>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Nama</dt>
                            <dd class="font-medium">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-medium">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">NIDN</dt>
                            <dd class="font-medium">{{ $user->nidn ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Fakultas</dt>
                            <dd class="font-medium">{{ $user->fakultas ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Mata Kuliah Diajar</dt>
                            <dd class="font-medium">{{ $data['mataKuliahList']->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Mahasiswa Bimbingan</dt>
                            <dd class="font-medium">{{ $data['bimbinganCount'] }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Mata Kuliah List --}}
                @if($data['mataKuliahList']->count())
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-md font-semibold mb-3">Mata Kuliah Diajar</h3>
                        <ul class="space-y-2">
                            @foreach($data['mataKuliahList'] as $mk)
                                <li class="border border-gray-200 rounded-md px-4 py-2 text-sm flex justify-between">
                                    <span>{{ $mk->nama }} ({{ $mk->kode }})</span>
                                    <span class="text-gray-500">{{ $mk->sks }} SKS</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection
