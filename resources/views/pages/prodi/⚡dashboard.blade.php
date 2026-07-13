@extends('layouts.app')

@section('content')
<div class="p-8 bg-soft-bg min-h-screen">
    <h1 class="text-2xl font-bold text-soft-dark mb-6">Dashboard Program Studi</h1>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-xs font-bold text-soft-muted uppercase">Total Mahasiswa</h3>
            <p class="text-3xl font-bold text-soft-dark mt-2">{{ $stats['total_siswa'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-xs font-bold text-soft-muted uppercase">Overload SKS (30 hari)</h3>
            <p class="text-3xl font-bold text-appleRed mt-2">{{ $stats['notif_overload_sks'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-xs font-bold text-soft-muted uppercase">Deadline Collision (30 hari)</h3>
            <p class="text-3xl font-bold text-pastel-ungu mt-2">{{ $stats['notif_deadline_collision'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-xs font-bold text-soft-muted uppercase">Notifikasi Lainnya (30 hari)</h3>
            <p class="text-3xl font-bold text-soft-dark mt-2">{{ $stats['notif_lainnya'] }}</p>
        </div>
    </div>

    {{-- Chart + Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-sm font-bold text-soft-dark mb-4">Tren Beban Keseluruhan 8 Minggu</h3>
            <canvas id="weeklyTrendChart" height="90"></canvas>
        </div>

        {{-- Load Distribution Chart --}}
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-sm font-bold text-soft-dark mb-4">Distribusi Beban Minggu Ini</h3>
            <canvas id="loadDistChart" height="200"></canvas>
        </div>

        {{-- Course Average Tasks Table --}}
        <div class="bg-white p-6 rounded-[24px] border border-soft-border shadow-sm animate-fade-in-up">
            <h3 class="text-sm font-bold text-soft-dark mb-4">Rata-rata Tugas/Minggu per Mata Kuliah</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-soft-border text-left text-xs font-bold text-soft-muted uppercase">
                            <th class="py-2 px-4">Mata Kuliah</th>
                            <th class="py-2 px-4 text-center">Rata-rata Tugas/Minggu</th>
                            <th class="py-2 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-soft-dark">
                        @foreach($courses as $course)
                            <tr class="border-b border-soft-border/50">
                                <td class="py-3 px-4">{{ $course['nama'] }}</td>
                                <td class="py-3 px-4 text-center">{{ $course['avg_tasks_week'] }}</td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $st = $course['status'];
                                        $cls = match($st) {
                                            'ringan' => 'bg-green-100 text-green-700',
                                            'normal' => 'bg-blue-100 text-blue-700',
                                            'berat' => 'bg-yellow-100 text-yellow-700',
                                            'overload' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $cls }}">{{ ucfirst($st) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const distData = @json($distribution);
    const trendData = @json($trend);
    new Chart(document.getElementById('weeklyTrendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(item => item.label),
            datasets: [
                { label: 'Ringan', data: trendData.map(item => item.ringan), borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.08)', tension: 0.35 },
                { label: 'Normal', data: trendData.map(item => item.normal), borderColor: '#3B82F6', backgroundColor: 'rgba(59, 130, 246, 0.08)', tension: 0.35 },
                { label: 'Berat', data: trendData.map(item => item.berat), borderColor: '#F59E0B', backgroundColor: 'rgba(245, 158, 11, 0.08)', tension: 0.35 },
                { label: 'Overload', data: trendData.map(item => item.overload), borderColor: '#EF4444', backgroundColor: 'rgba(239, 68, 68, 0.08)', tension: 0.35 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('loadDistChart'), {
        type: 'doughnut',
        data: {
            labels: ['Ringan', 'Normal', 'Berat', 'Overload'],
            datasets: [{
                data: [
                    distData.ringan ?? 0,
                    distData.normal ?? 0,
                    distData.berat ?? 0,
                    distData.overload ?? 0,
                ],
                backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.parsed} mahasiswa`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
