@extends('layouts.app')

@section('content')
@php
    $cardClass = 'bg-white border border-bone-dark rounded-2xl shadow-sm transition-all duration-300';
    $mutedClass = 'text-appleMuted';
    $inputClass = 'w-full rounded-xl border border-bone-dark bg-white px-3 py-2 text-sm focus:border-appleDark focus:outline-none';
    $btnPrimary = 'rounded-xl bg-appleDark px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-appleDark/20 hover:opacity-90 transition';

    $fieldValue = function ($record, string $field, array $fieldConfig) use ($options) {
        $value = $record->{$field};
        if (($fieldConfig['type'] ?? null) === 'checkbox') return $value ? 'Ya' : 'Tidak';
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

    $navIcons = [
        'siswas' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'dosens' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
        'admins' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'mata-kuliah' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'krs' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
        'dosen-pa' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
        'ipk-history' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'notifikasi' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'notifikasi-dosen' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'kalender-akademik' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'laporan' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'pengaturan' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z',
    ];
@endphp

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-backdrop').classList.toggle('hidden');
    }
</script>

<div class="min-h-screen bg-bone-light lg:grid lg:grid-cols-[16rem_1fr]">
    {{-- ═══ SIDEBAR ═══ --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-bone-dark bg-white/85 backdrop-blur-xl -translate-x-full lg:relative lg:w-auto lg:translate-x-0 transition-transform duration-200">
        {{-- Logo --}}
        <div class="flex h-14 items-center gap-2 border-b border-bone-dark px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-appleDark">
                <span class="text-xs font-bold text-white">E</span>
            </div>
            <span class="text-sm font-bold text-appleDark">edutrack daily</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest {{ $mutedClass }}">Data Master</p>
            @foreach ($resources as $key => $resource)
                <a href="{{ route('admin.dashboard', ['resource' => $key]) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                          {{ $resourceKey === $key
                              ? 'bg-appleDark text-white font-semibold shadow-sm shadow-appleDark/20'
                              : $mutedClass . ' hover:bg-bone hover:text-appleDark' }}">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $navIcons[$key] ?? $navIcons['pengaturan'] }}" />
                    </svg>
                    <span class="flex-1 truncate">{{ $resource['label'] }}</span>
                    <span class="text-xs {{ $resourceKey === $key ? 'text-white/60' : $mutedClass }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach

            <div class="my-3 border-t border-bone-dark"></div>
            <a href="{{ route('admin.laporan.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                      {{ $resourceKey === 'laporan'
                          ? 'bg-appleDark text-white font-semibold shadow-sm shadow-appleDark/20'
                          : $mutedClass . ' hover:bg-bone hover:text-appleDark' }}">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $navIcons['laporan'] }}" />
                </svg>
                <span>Laporan</span>
            </a>
        </nav>

        {{-- User + Logout --}}
        <div class="border-t border-bone-dark px-3 py-3">
            <div class="flex items-center gap-3 rounded-xl bg-bone px-3 py-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-appleDark text-xs font-bold text-white ring-4 ring-white">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-appleDark">{{ $user->name }}</p>
                    <p class="truncate text-[11px] {{ $mutedClass }}">Admin</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm text-appleRed transition-colors hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- ═══ MAIN ═══ --}}
    <main class="flex flex-1 flex-col overflow-y-auto">
        {{-- Sticky Header --}}
        <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-bone-dark bg-bone-light/90 px-5 py-4 backdrop-blur-lg lg:px-8">
            <button onclick="toggleSidebar()" class="lg:hidden text-appleDark">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <div class="min-w-0 flex-1">
                <h1 class="text-lg font-bold tracking-tight text-appleDark">Dashboard Admin</h1>
                <p class="text-xs {{ $mutedClass }}">{{ $config['label'] ?? 'Data Master' }} — Kelola data sistem.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-appleDark text-xs font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="space-y-6 p-5 pb-12 lg:p-8">
            @if (session('status'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-appleRed/40 bg-red-50 px-5 py-3 text-sm text-red-800">
                    <p class="font-semibold">Data belum valid.</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Laporan Generator --}}
            @if ($resourceKey === 'laporan')
                <section class="{{ $cardClass }} p-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-4">Generate Laporan Akademik</p>
                    <form method="POST" action="{{ route('admin.laporan.generate') }}" class="space-y-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Periode Mulai</label>
                                <input type="date" name="start_date" class="{{ $inputClass }}" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Periode Akhir</label>
                                <input type="date" name="end_date" class="{{ $inputClass }}" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Filter Prodi (opsional)</label>
                                <select name="prodi" class="{{ $inputClass }}">
                                    <option value="">Semua Prodi</option>
                                    @foreach($laporanProdis ?? [] as $prodi)
                                        <option value="{{ $prodi }}">{{ $prodi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="tipe" value="akademik">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="{{ $btnPrimary }}">Generate Laporan</button>
                        </div>
                    </form>
                </section>
            @endif

            {{-- KRS Batch Form --}}
            @if ($resourceKey === 'krs' && ! $editing)
                <section class="{{ $cardClass }} p-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-1">Tambah KRS Paket</p>
                    <p class="text-xs {{ $mutedClass }} mb-4">Pilih paket berdasarkan semester dan tahun ajaran.</p>

                    <form method="POST" action="{{ route('admin.resources.store', $resourceKey) }}" class="grid gap-4 md:grid-cols-3">
                        @csrf
                        <input type="hidden" name="_mode" value="batch">

                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Siswa <span class="text-appleRed">*</span></label>
                            <select name="siswa_id" class="{{ $inputClass }}" required>
                                <option value="">Pilih Siswa</option>
                                @foreach ($options['siswas'] as $siswa)
                                    <option value="{{ $siswa->id }}" @selected((string) old('siswa_id') === (string) $siswa->id)>
                                        {{ $siswa->name }} @if ($siswa->email ?? null) - {{ $siswa->email }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Paket KRS <span class="text-appleRed">*</span></label>
                            <select name="krs_package" class="{{ $inputClass }}" required>
                                <option value="">Pilih Paket KRS</option>
                                @foreach ($options['krs_packages'] as $package)
                                    <option value="{{ $package['key'] }}" @selected(old('krs_package') === $package['key'])>
                                        {{ $package['label'] }} ({{ $package['total'] }} mata kuliah)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Status <span class="text-appleRed">*</span></label>
                            <input type="text" name="status" value="{{ old('status', 'aktif') }}" class="{{ $inputClass }}" required>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="{{ $btnPrimary }}">Tambahkan Paket KRS</button>
                        </div>
                    </form>
                </section>
            @endif

            {{-- Create / Edit Form --}}
            <section class="{{ $cardClass }} p-5">
                <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">{{ $editing ? 'Edit' : 'Tambah' }} {{ $config['label'] }}</p>
                    @if ($editing)
                        <a href="{{ route('admin.dashboard', ['resource' => $resourceKey]) }}" class="text-sm font-semibold text-appleDark hover:underline">Batal edit</a>
                    @endif
                </div>

                <form method="POST" action="{{ $formAction }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($editing) @method('PUT') @endif

                    @foreach ($config['fields'] as $field => $fieldConfig)
                        <label class="{{ ($fieldConfig['type'] ?? null) === 'textarea' ? 'md:col-span-2' : '' }} block">
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false) <span class="text-appleRed">*</span> @endif
                            </span>

                            @if (($fieldConfig['type'] ?? null) === 'select')
                                <select name="{{ $field }}" class="{{ $inputClass }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                                    <option value="">Pilih {{ $fieldConfig['label'] }}</option>
                                    @foreach ($options[$fieldConfig['options']] as $option)
                                        <option value="{{ $option->id }}" @selected((string) $inputValue($field) === (string) $option->id)>
                                            {{ $option->name ?? $option->nama }} @if ($option->email ?? null) - {{ $option->email }} @elseif ($option->kode ?? null) - {{ $option->kode }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            @elseif (($fieldConfig['type'] ?? null) === 'textarea')
                                <textarea name="{{ $field }}" rows="3" class="{{ $inputClass }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>{{ $inputValue($field) }}</textarea>
                            @elseif (($fieldConfig['type'] ?? null) === 'checkbox')
                                <input type="hidden" name="{{ $field }}" value="0">
                                <label class="flex items-center gap-2 rounded-xl border border-bone-dark px-3 py-2 text-sm">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked((bool) $inputValue($field)) class="rounded border-bone-dark text-appleDark focus:ring-appleDark">
                                    Aktif
                                </label>
                            @else
                                <input
                                    type="{{ $fieldConfig['type'] ?? 'text' }}"
                                    name="{{ $field }}"
                                    value="{{ ($fieldConfig['type'] ?? null) === 'password' ? '' : $inputValue($field) }}"
                                    class="{{ $inputClass }}"
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
                        <button type="submit" class="{{ $btnPrimary }}">{{ $editing ? 'Simpan Perubahan' : 'Tambah Data' }}</button>
                    </div>
                </form>
            </section>

            {{-- Data Listing --}}
            <section class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-bone-dark px-5 py-4">
                    <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }}">Daftar {{ $config['label'] }}</p>
                    @if ($resourceKey === 'krs' && $krsGroups)
                        <p class="text-xs {{ $mutedClass }} mt-1">Total {{ $krsGroups->total() }} murid dengan {{ $records->total() }} data KRS.</p>
                    @elseif ($resourceKey === 'ipk-history' && $ipkHistoryGroups)
                        <p class="text-xs {{ $mutedClass }} mt-1">Total {{ $ipkHistoryGroups->total() }} murid dengan {{ $records->total() }} data IPK History.</p>
                    @else
                        <p class="text-xs {{ $mutedClass }} mt-1">Total {{ $records->total() }} data.</p>
                    @endif
                </div>

                @if ($resourceKey === 'krs' && $krsGroups)
                    <div class="divide-y divide-bone-dark/70">
                        @forelse ($krsGroups as $siswa)
                            @php
                                $krsList = $siswa->krs;
                                $totalSks = $krsList->sum(fn ($krs) => (int) ($krs->mataKuliah?->sks ?? 0));
                                $tahunAjaran = $krsList->pluck('tahun_ajaran')->filter()->unique()->values()->join(', ');
                                $semester = $krsList->pluck('semester')->filter()->unique()->sort()->values()->join(', ');
                            @endphp
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 hover:bg-bone-light/70 transition-colors">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-appleDark">{{ $siswa->name }}</span>
                                            <span class="rounded-full bg-bone px-2 py-0.5 text-xs font-bold {{ $mutedClass }} ring-1 ring-bone-dark/50">{{ $krsList->count() }} mata kuliah</span>
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-bold text-appleGreen ring-1 ring-green-200">{{ $totalSks }} SKS</span>
                                        </div>
                                        <p class="mt-1 truncate text-xs {{ $mutedClass }}">{{ $siswa->nim ?? '-' }} · Semester {{ $semester ?: '-' }} · {{ $tahunAjaran ?: '-' }}</p>
                                    </div>
                                    <svg class="h-4 w-4 flex-shrink-0 {{ $mutedClass }} transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </summary>
                                <div class="border-t border-bone-dark bg-bone px-5 py-4">
                                    <div class="overflow-x-auto rounded-xl border border-bone-dark bg-white">
                                        <table class="min-w-full divide-y divide-bone-dark/70 text-xs">
                                            <thead class="bg-bone text-left">
                                                <tr class="{{ $mutedClass }}">
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Mata Kuliah</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">SKS</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Semester</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Tahun Ajaran</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Status</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px] text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-bone-dark/50">
                                                @foreach ($krsList as $krs)
                                                    <tr class="hover:bg-bone-light/70 transition-colors">
                                                        <td class="px-4 py-2">
                                                            <span class="font-semibold text-appleDark">{{ $krs->mataKuliah?->nama ?? '-' }}</span>
                                                            <span class="block text-[11px] {{ $mutedClass }}">{{ $krs->mataKuliah?->kode ?? '-' }}</span>
                                                        </td>
                                                        <td class="px-4 py-2 font-semibold text-appleDark">{{ $krs->mataKuliah?->sks ?? 0 }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $krs->semester }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $krs->tahun_ajaran }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $krs->status }}</td>
                                                        <td class="px-4 py-2">
                                                            <div class="flex justify-end gap-2">
                                                                <a href="{{ route('admin.dashboard', ['resource' => $resourceKey, 'edit' => $krs->id]) }}" class="rounded-lg border border-bone-dark px-2 py-1 text-[11px] font-semibold text-appleDark hover:bg-bone">Edit</a>
                                                                <form method="POST" action="{{ route('admin.resources.destroy', [$resourceKey, $krs->id]) }}" onsubmit="return confirm('Hapus KRS ini?')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="rounded-lg border border-appleRed/40 px-2 py-1 text-[11px] font-semibold text-appleRed hover:bg-red-50">Hapus</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="px-5 py-8 text-center text-sm {{ $mutedClass }}">Belum ada data.</p>
                        @endforelse
                    </div>
                    <div class="border-t border-bone-dark px-5 py-4">{{ $krsGroups->links() }}</div>

                @elseif ($resourceKey === 'ipk-history' && $ipkHistoryGroups)
                    {{-- IPK Auto Calculator --}}
                    <div class="border-b border-bone-dark bg-bone px-5 py-4">
                        <p class="text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-3">Kalkulasi IPK Otomatis dari KRS</p>
                        @if($errors->has('ipk_auto'))
                            <div class="mb-3 rounded-xl border border-appleRed/40 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('ipk_auto') }}</div>
                        @endif
                        <form method="POST" action="{{ route('admin.ipk-history.generate-auto') }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                            @csrf
                            <div class="w-full sm:w-auto">
                                <label class="block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-1">Siswa</label>
                                <select name="siswa_id" required class="{{ $inputClass }} sm:w-64">
                                    <option value="">Pilih Siswa...</option>
                                    @foreach($options['siswas'] ?? [] as $s)
                                        <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->nim }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:w-auto">
                                <label class="block text-[11px] font-bold uppercase tracking-widest {{ $mutedClass }} mb-1">Semester</label>
                                <input type="number" name="semester" min="1" max="14" value="{{ old('semester', 1) }}" required class="{{ $inputClass }} sm:w-24">
                            </div>
                            <button type="submit" class="{{ $btnPrimary }} whitespace-nowrap">Hitung & Simpan IPK</button>
                        </form>
                    </div>

                    <div class="divide-y divide-bone-dark/70">
                        @forelse ($ipkHistoryGroups as $siswa)
                            @php
                                $historyList = $siswa->ipkHistory;
                                $totalSks = $historyList->sum(fn ($h) => (int) ($h->total_sks ?? 0));
                                $avgIpk = $historyList->avg('ipk');
                            @endphp
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 hover:bg-bone-light/70 transition-colors">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-appleDark">{{ $siswa->name }}</span>
                                            <span class="rounded-full bg-bone px-2 py-0.5 text-xs font-bold {{ $mutedClass }} ring-1 ring-bone-dark/50">{{ $historyList->count() }} semester</span>
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-bold text-appleGreen ring-1 ring-green-200">IPK {{ number_format((float) $avgIpk, 2) }}</span>
                                        </div>
                                        <p class="mt-1 truncate text-xs {{ $mutedClass }}">{{ $siswa->nim ?? '-' }} · {{ $totalSks }} SKS total</p>
                                    </div>
                                    <svg class="h-4 w-4 flex-shrink-0 {{ $mutedClass }} transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </summary>
                                <div class="border-t border-bone-dark bg-bone px-5 py-4">
                                    <div class="overflow-x-auto rounded-xl border border-bone-dark bg-white">
                                        <table class="min-w-full divide-y divide-bone-dark/70 text-xs">
                                            <thead class="bg-bone text-left">
                                                <tr class="{{ $mutedClass }}">
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Semester</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Tahun Ajaran</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">IPK</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Total SKS</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px]">Rekomendasi SKS</th>
                                                    <th class="px-4 py-2 font-bold uppercase tracking-widest text-[10px] text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-bone-dark/50">
                                                @foreach ($historyList as $history)
                                                    <tr class="hover:bg-bone-light/70 transition-colors">
                                                        <td class="px-4 py-2 font-semibold text-appleDark">{{ $history->semester }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $history->tahun_ajaran }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ number_format((float) $history->ipk, 2) }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $history->total_sks }}</td>
                                                        <td class="px-4 py-2 {{ $mutedClass }}">{{ $history->rekomendasi_sks ?? '-' }}</td>
                                                        <td class="px-4 py-2">
                                                            <div class="flex justify-end gap-2">
                                                                <a href="{{ route('admin.dashboard', ['resource' => $resourceKey, 'edit' => $history->id]) }}" class="rounded-lg border border-bone-dark px-2 py-1 text-[11px] font-semibold text-appleDark hover:bg-bone">Edit</a>
                                                                <form method="POST" action="{{ route('admin.resources.destroy', [$resourceKey, $history->id]) }}" onsubmit="return confirm('Hapus IPK History ini?')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="rounded-lg border border-appleRed/40 px-2 py-1 text-[11px] font-semibold text-appleRed hover:bg-red-50">Hapus</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="px-5 py-8 text-center text-sm {{ $mutedClass }}">Belum ada data.</p>
                        @endforelse
                    </div>
                    <div class="border-t border-bone-dark px-5 py-4">{{ $ipkHistoryGroups->links() }}</div>

                @else
                    {{-- Generic table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-bone-dark/70 text-sm">
                            <thead class="bg-bone text-left">
                                <tr class="{{ $mutedClass }}">
                                    <th class="px-5 py-3 font-bold uppercase tracking-widest text-[10px]">ID</th>
                                    @foreach ($config['fields'] as $field => $fieldConfig)
                                        @continue($fieldConfig['hide_table'] ?? false)
                                        <th class="px-5 py-3 font-bold uppercase tracking-widest text-[10px]">{{ $fieldConfig['label'] }}</th>
                                    @endforeach
                                    <th class="px-5 py-3 font-bold uppercase tracking-widest text-[10px] text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bone-dark/50">
                                @forelse ($records as $record)
                                    <tr class="hover:bg-bone-light/70 transition-colors">
                                        <td class="px-5 py-3 font-mono text-xs {{ $mutedClass }}">{{ $record->id }}</td>
                                        @foreach ($config['fields'] as $field => $fieldConfig)
                                            @continue($fieldConfig['hide_table'] ?? false)
                                            <td class="max-w-xs truncate px-5 py-3">{{ $fieldValue($record, $field, $fieldConfig) }}</td>
                                        @endforeach
                                        <td class="px-5 py-3">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.dashboard', ['resource' => $resourceKey, 'edit' => $record->id]) }}" class="rounded-lg border border-bone-dark px-3 py-1.5 text-xs font-semibold text-appleDark hover:bg-bone">Edit</a>
                                                <form method="POST" action="{{ route('admin.resources.destroy', [$resourceKey, $record->id]) }}" onsubmit="return confirm('Hapus data ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-appleRed/40 px-3 py-1.5 text-xs font-semibold text-appleRed hover:bg-red-50">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count(array_filter($config['fields'], fn ($field) => ! ($field['hide_table'] ?? false))) + 2 }}" class="px-5 py-8 text-center {{ $mutedClass }}">
                                            Belum ada data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-bone-dark px-5 py-4">{{ $records->links() }}</div>
                @endif
            </section>
        </div>
    </main>
</div>
@endsection
