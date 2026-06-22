<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout()
    {
        Auth::guard('siswa')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }
}; ?>

<div class="flex h-screen bg-bone-light text-appleDark font-sans overflow-hidden selection:bg-appleDark selection:text-white">
    
    <aside class="w-64 bg-white/60 backdrop-blur-xl border-r border-bone-dark/60 flex flex-col justify-between z-20 flex-shrink-0">
        <div class="p-6 space-y-8">
            <div class="px-2">
                <span class="text-xl font-bold tracking-tighter lowercase select-none block">EduTrack</span>
                <span class="text-[10px] font-bold text-appleMuted uppercase tracking-widest mt-1">Monitoring SKS</span>
            </div>

            <nav class="space-y-1.5">
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] bg-appleDark text-white text-sm font-medium shadow-sm transition-all">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Kalender Tugas
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Monitoring SKS
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-appleMuted hover:bg-bone-dark/50 hover:text-appleDark text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Analitik Akademik
                </a>
            </nav>
        </div>
        
        <div class="p-6 border-t border-bone-dark/50 space-y-1.5">
            <button wire:click="logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-appleRed hover:bg-red-50 text-sm font-medium transition-all text-left">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar Sesi
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen relative">
        
        <header class="h-20 bg-bone-light/80 backdrop-blur-xl border-b border-bone-dark/50 px-8 flex items-center justify-between z-10 sticky top-0">
            <div class="relative w-full max-w-md">
                <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-appleMuted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari tugas, mata kuliah..." class="w-full bg-white border border-bone-dark rounded-full pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:border-appleDark focus:ring-1 focus:ring-appleDark transition-all placeholder:text-appleMuted">
            </div>

            <div class="flex items-center gap-4 ml-4">
                <button class="relative p-2 text-appleMuted hover:text-appleDark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-appleRed rounded-full border-2 border-bone-light"></span>
                </button>
                <div class="flex items-center gap-3 pl-4 border-l border-bone-dark">
                    <div class="w-9 h-9 rounded-full bg-appleDark text-white flex items-center justify-center text-xs font-bold shadow-sm">
                        {{ substr(Auth::guard('siswa')->user()->name ?? 'MH', 0, 2) }}
                    </div>
                    <div class="hidden md:block">
                        <p class="text-xs font-bold text-appleDark leading-tight">{{ Auth::guard('siswa')->user()->name ?? 'Mahasiswa' }}</p>
                        <p class="text-[10px] text-appleMuted">ID: 203480234</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto no-scrollbar p-6 md:p-8 space-y-6 pb-24">
            
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-appleDark">Dashboard Akademik</h1>
                <p class="text-sm text-appleMuted mt-1">Monitoring beban akademik dan deadline tugas Anda</p>
            </div>

            <div class="bg-[#FFF4E5] border border-[#FFE0B2] rounded-[24px] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="bg-appleOrange/20 text-appleOrange p-2 rounded-full mt-0.5 sm:mt-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-appleDark">Peringatan: Beban Akademik Tinggi</h3>
                        <p class="text-xs text-appleDark/70 mt-0.5 leading-relaxed">Beban akademik Anda minggu ini melebihi batas aman. Terdapat 8 tugas dengan 3 deadline dalam 3 hari ke depan.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="bg-appleDark text-white px-4 py-2 rounded-full text-xs font-bold tracking-tight hover:bg-appleDark/90 transition-all active:scale-95">Lihat Detail</button>
                    <button class="bg-white border border-appleDark/20 text-appleDark px-4 py-2 rounded-full text-xs font-bold tracking-tight hover:bg-bone-dark/50 transition-all active:scale-95">Konsultasi Dosen PA</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm space-y-4">
                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-appleDark">21</h2>
                        <p class="text-xs font-bold uppercase text-appleDark tracking-wide mt-1">Total SKS Aktif</p>
                        <p class="text-[11px] text-appleMuted mt-0.5">Semester ini</p>
                    </div>
                </div>
                <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm space-y-4">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-appleGreen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-appleDark">8</h2>
                        <p class="text-xs font-bold uppercase text-appleDark tracking-wide mt-1">Tugas Minggu Ini</p>
                        <p class="text-[11px] text-appleMuted mt-0.5">2 belum selesai</p>
                    </div>
                </div>
                <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm space-y-4">
                    <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center text-appleOrange">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-appleDark">2</h2>
                        <p class="text-xs font-bold uppercase text-appleDark tracking-wide mt-1">Deadline Terdekat</p>
                        <p class="text-[11px] text-appleMuted mt-0.5">Hari ini</p>
                    </div>
                </div>
                <div class="bg-white border border-bone-dark rounded-[24px] p-5 shadow-sm space-y-4">
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-appleRed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-appleDark">Berat</h2>
                        <p class="text-xs font-bold uppercase text-appleDark tracking-wide mt-1">Status Beban</p>
                        <p class="text-[11px] text-appleMuted mt-0.5">Perlu perhatian</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                
                <div class="lg:col-span-2 bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-appleDark">Beban Akademik Minggu Ini</h3>
                        <p class="text-xs text-appleMuted mt-0.5">Distribusi tugas dan intensitas beban per hari</p>
                        
                        <div class="flex items-center gap-4 mt-4">
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#34C759]"></span><span class="text-[10px] font-bold uppercase tracking-wider text-appleMuted">Ringan</span></div>
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#FF9500]"></span><span class="text-[10px] font-bold uppercase tracking-wider text-appleMuted">Normal</span></div>
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span><span class="text-[10px] font-bold uppercase tracking-wider text-appleMuted">Berat</span></div>
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#5856D6]"></span><span class="text-[10px] font-bold uppercase tracking-wider text-appleMuted">Overload</span></div>
                        </div>
                    </div>

                    <div class="flex items-end justify-between h-40 mt-8 gap-2">
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#FF9500] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 45%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Sen</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#FF9500] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 60%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Sel</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#34C759] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 25%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Rab</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#FF3B30] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 90%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Kam</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#FF9500] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 70%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Jum</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-[#34C759] rounded-t-[4px] hover:opacity-80 transition-opacity" style="height: 15%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Sab</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-2">
                            <div class="w-full bg-bone-dark/50 rounded-t-[4px]" style="height: 2%;"></div>
                            <span class="text-[10px] font-medium text-appleMuted">Min</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-appleDark">Timeline Deadline</h3>
                            <p class="text-xs text-appleMuted mt-0.5">Tugas yang akan datang</p>
                        </div>
                        <a href="#" class="text-[11px] font-bold text-blue-500 hover:text-blue-600 transition-colors">Lihat Semua</a>
                    </div>
                    
                    <div class="space-y-5 mt-6 relative before:absolute before:inset-0 before:ml-2 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-bone-dark before:to-transparent">
                        
                        <div class="relative flex items-start gap-4">
                            <div class="w-4 h-4 rounded-full bg-white border-[4px] border-[#FF3B30] shrink-0 mt-0.5 z-10 shadow-sm"></div>
                            <div>
                                <h4 class="text-xs font-bold text-appleDark">Tugas Algoritma & Struktur Data</h4>
                                <p class="text-[10px] text-blue-500 font-medium mt-0.5">Algoritma & Struktur Data</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] text-appleMuted flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 15 Mei 2026</span>
                                    <span class="text-[10px] font-bold text-[#FF3B30]">1 hari lagi</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative flex items-start gap-4">
                            <div class="w-4 h-4 rounded-full bg-white border-[4px] border-[#FF9500] shrink-0 mt-0.5 z-10 shadow-sm"></div>
                            <div>
                                <h4 class="text-xs font-bold text-appleDark">Paper Review Database</h4>
                                <p class="text-[10px] text-blue-500 font-medium mt-0.5">Basis Data</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] text-appleMuted flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 17 Mei 2026</span>
                                    <span class="text-[10px] font-bold text-[#FF9500]">3 hari lagi</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative flex items-start gap-4">
                            <div class="w-4 h-4 rounded-full bg-white border-[4px] border-[#34C759] shrink-0 mt-0.5 z-10 shadow-sm"></div>
                            <div>
                                <h4 class="text-xs font-bold text-appleDark">Presentasi Sistem Operasi</h4>
                                <p class="text-[10px] text-blue-500 font-medium mt-0.5">Sistem Operasi</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] text-appleMuted flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 20 Mei 2026</span>
                                    <span class="text-[10px] font-bold text-[#34C759]">6 hari lagi</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative flex items-start gap-4">
                            <div class="w-4 h-4 rounded-full bg-white border-[4px] border-blue-500 shrink-0 mt-0.5 z-10 shadow-sm"></div>
                            <div>
                                <h4 class="text-xs font-bold text-appleDark">Quiz Jaringan Komputer</h4>
                                <p class="text-[10px] text-blue-500 font-medium mt-0.5">Jaringan Komputer</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] text-appleMuted flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 22 Mei 2026</span>
                                    <span class="text-[10px] font-bold text-blue-500">8 hari lagi</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</div>