@extends('layouts.app')

@section('content')
<div class="p-8 bg-bone-light min-h-screen">
    <h1 class="text-2xl font-bold text-appleDark mb-6">Dashboard Program Studi</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-[24px] border border-bone-dark shadow-sm">
            <h3 class="text-xs font-bold text-appleMuted uppercase">Total Mahasiswa</h3>
            <p class="text-3xl font-bold text-appleDark mt-2">{{ $stats['total_siswa'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-bone-dark shadow-sm">
            <h3 class="text-xs font-bold text-appleMuted uppercase">Notifikasi 30 Hari</h3>
            <p class="text-3xl font-bold text-appleDark mt-2">{{ $stats['total_notifikasi_30d'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-bone-dark shadow-sm">
            <h3 class="text-xs font-bold text-appleMuted uppercase">Total Mata Kuliah</h3>
            <p class="text-3xl font-bold text-appleDark mt-2">{{ $stats['total_matkul'] }}</p>
        </div>
    </div>
</div>
@endsection
