@extends('layouts.app')

@section('content')
@php
    $fieldValue = function ($record, string $field, array $fieldConfig) use ($options) {
        $value = $record->{$field};

        if (($fieldConfig['type'] ?? null) === 'checkbox') {
            return $value ? 'Ya' : 'Tidak';
        }

        if (isset($fieldConfig['options'])) {
            $option = $options[$fieldConfig['options']]?->firstWhere('id', $value);

            return $option?->name ?? $option?->nama ?? $option?->judul ?? $value;
        }

        return $value ?? '-';
    };

    $inputValue = fn (string $field) => old($field, $editing?->{$field});
    $formAction = $editing
        ? route('admin.resources.update', [$resourceKey, $editing->id])
        : route('admin.resources.store', $resourceKey);
@endphp

<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="mx-auto max-w-7xl p-6">
        <header class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-500">Halo, {{ $user->name }}</p>
                <h1 class="text-2xl font-bold">Dashboard Admin</h1>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    Logout
                </button>
            </form>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Data belum valid.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($resourceKey === 'laporan')
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm mb-6">
                <h2 class="text-lg font-semibold mb-4">Generate Laporan Akademik</h2>
                <form method="POST" action="{{ route('admin.laporan.generate') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Periode Mulai</label>
                            <input type="date" name="start_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Periode Akhir</label>
                            <input type="date" name="end_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Filter Prodi (opsional)</label>
<select name="prodi" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none">
    <option value="">Semua Prodi</option>
    @foreach($laporanProdis ?? [] as $prodi)
        <option value="{{ $prodi }}">{{ $prodi }}</option>
    @endforeach
</select>
                            </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            Generate Laporan
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
            <aside class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Data Master</h2>
                <nav class="space-y-1">
                    @foreach ($resources as $key => $resource)
                        <a
                            href="{{ route('admin.dashboard', ['resource' => $key]) }}"
                            class="flex items-center justify-between rounded-md px-3 py-2 text-sm {{ $resourceKey === $key ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
                        >
                            <span>{{ $resource['label'] }}</span>
                            <span class="text-xs {{ $resourceKey === $key ? 'text-gray-300' : 'text-gray-400' }}">{{ $counts[$key] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <main class="space-y-6">
                @if ($resourceKey === 'krs' && ! $editing)
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold">Tambah KRS Paket</h2>
                            <p class="text-sm text-gray-500">Pilih paket berdasarkan semester dan tahun ajaran, lalu masukkan semua mata kuliah paket ke satu siswa.</p>
                        </div>

                        <form method="POST" action="{{ route('admin.resources.store', $resourceKey) }}" class="grid gap-4 md:grid-cols-3">
                            @csrf
                            <input type="hidden" name="_mode" value="batch">

                            <label class="block">
                                <span class="mb-1 block text-sm font-medium text-gray-700">Siswa <span class="text-red-600">*</span></span>
                                <select
                                    name="siswa_id"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                    required
                                >
                                    <option value="">Pilih Siswa</option>
                                    @foreach ($options['siswas'] as $siswa)
                                        <option value="{{ $siswa->id }}" @selected((string) old('siswa_id') === (string) $siswa->id)>
                                            {{ $siswa->name }} @if ($siswa->email ?? null) - {{ $siswa->email }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-sm font-medium text-gray-700">Paket KRS <span class="text-red-600">*</span></span>
                                <select
                                    name="krs_package"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                    required
                                >
                                    <option value="">Pilih Paket KRS</option>
                                    @foreach ($options['krs_packages'] as $package)
                                        <option value="{{ $package['key'] }}" @selected(old('krs_package') === $package['key'])>
                                            {{ $package['label'] }} ({{ $package['total'] }} mata kuliah)
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-sm font-medium text-gray-700">Status <span class="text-red-600">*</span></span>
                                <input
                                    type="text"
                                    name="status"
                                    value="{{ old('status', 'aktif') }}"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                    required
                                >
                            </label>

                            <div class="md:col-span-3">
                                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    Tambahkan Paket KRS
                                </button>
                            </div>
                        </form>
                    </section>
                @endif

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $editing ? 'Edit' : 'Tambah' }} {{ $config['label'] }}</h2>
                            <p class="text-sm text-gray-500">Isi field sesuai struktur database saat ini.</p>
                        </div>
                        @if ($editing)
                            <a href="{{ route('admin.dashboard', ['resource' => $resourceKey]) }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900">
                                Batal edit
                            </a>
                        @endif
                    </div>

                    <form method="POST" action="{{ $formAction }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        @if ($editing)
                            @method('PUT')
                        @endif

                        @foreach ($config['fields'] as $field => $fieldConfig)
                            <label class="{{ ($fieldConfig['type'] ?? null) === 'textarea' ? 'md:col-span-2' : '' }} block">
                                <span class="mb-1 block text-sm font-medium text-gray-700">
                                    {{ $fieldConfig['label'] }}
                                    @if ($fieldConfig['required'] ?? false)
                                        <span class="text-red-600">*</span>
                                    @endif
                                </span>

                                @if (($fieldConfig['type'] ?? null) === 'select')
                                    <select
                                        name="{{ $field }}"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                        {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}
                                    >
                                        <option value="">Pilih {{ $fieldConfig['label'] }}</option>
                                        @foreach ($options[$fieldConfig['options']] as $option)
                                            <option value="{{ $option->id }}" @selected((string) $inputValue($field) === (string) $option->id)>
                                                {{ $option->name ?? $option->nama }} @if ($option->email ?? null) - {{ $option->email }} @elseif ($option->kode ?? null) - {{ $option->kode }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif (($fieldConfig['type'] ?? null) === 'textarea')
                                    <textarea
                                        name="{{ $field }}"
                                        rows="3"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                        {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}
                                    >{{ $inputValue($field) }}</textarea>
                                @elseif (($fieldConfig['type'] ?? null) === 'checkbox')
                                    <input type="hidden" name="{{ $field }}" value="0">
                                    <label class="flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <input type="checkbox" name="{{ $field }}" value="1" @checked((bool) $inputValue($field))>
                                        Aktif
                                    </label>
                                @else
                                    <input
                                        type="{{ $fieldConfig['type'] ?? 'text' }}"
                                        name="{{ $field }}"
                                        value="{{ ($fieldConfig['type'] ?? null) === 'password' ? '' : $inputValue($field) }}"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                        min="{{ $fieldConfig['min'] ?? '' }}"
                                        max="{{ $fieldConfig['max'] ?? '' }}"
                                        step="{{ $fieldConfig['step'] ?? '' }}"
                                        placeholder="{{ ($fieldConfig['type'] ?? null) === 'password' && $editing ? 'Kosongkan jika tidak diubah' : '' }}"
                                        {{ (($fieldConfig['required'] ?? false) && ! $editing) || (($fieldConfig['required'] ?? false) && ($fieldConfig['type'] ?? null) !== 'password') ? 'required' : '' }}
                                    >
                                @endif
                            </label>
                        @endforeach

                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                {{ $editing ? 'Simpan Perubahan' : 'Tambah Data' }}
                            </button>
                        </div>
                    </form>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 p-5">
                        <div>
                            <h2 class="text-lg font-semibold">Daftar {{ $config['label'] }}</h2>
                            <p class="text-sm text-gray-500">Total {{ $records->total() }} data.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    @foreach ($config['fields'] as $field => $fieldConfig)
                                        @continue($fieldConfig['hide_table'] ?? false)
                                        <th class="px-4 py-3">{{ $fieldConfig['label'] }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($records as $record)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $record->id }}</td>
                                        @foreach ($config['fields'] as $field => $fieldConfig)
                                            @continue($fieldConfig['hide_table'] ?? false)
                                            <td class="max-w-xs truncate px-4 py-3">{{ $fieldValue($record, $field, $fieldConfig) }}</td>
                                        @endforeach
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-2">
                                                <a
                                                    href="{{ route('admin.dashboard', ['resource' => $resourceKey, 'edit' => $record->id]) }}"
                                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                                >
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.resources.destroy', [$resourceKey, $record->id]) }}" onsubmit="return confirm('Hapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count(array_filter($config['fields'], fn ($field) => ! ($field['hide_table'] ?? false))) + 2 }}" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 p-4">
                        {{ $records->links() }}
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
