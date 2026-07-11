@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col justify-between bg-[#FFFBF2] text-[#2E2A47] antialiased selection:bg-[#A29BFE] selection:text-white font-body relative overflow-hidden">
    
    <div class="fixed border-radius-full blur-[80px] pointer-events-none z-0 opacity-55" style="width: 520px; height: 520px; background: #FFF2CA; top: -160px; left: -140px;"></div>
    <div class="fixed border-radius-full blur-[80px] pointer-events-none z-0 opacity-55" style="width: 480px; height: 480px; background: #92C9FF; bottom: -180px; right: -120px;"></div>
    <div class="fixed inset-0 z-[1] pointer-events-none opacity-[0.02]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Cfilter id=\'n\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.9\' numOctaves=\'2\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23n)\'/%3E%3C/svg%3E');"></div>

    <header class="w-full bg-[#FFFBF2]/70 backdrop-blur-xl border-b border-[#2E2A47]/[0.06] px-4 md:px-16 py-4 md:py-5 flex flex-col sm:flex-row justify-center sm:justify-between items-center gap-2 sm:gap-0 z-50 fixed top-0 left-0 right-0">
        <a href="/" class="text-xl font-bold tracking-tighter lowercase select-none hover:opacity-80 transition-opacity"><x-title/></a>
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-[#726D8C] font-mono">Portal Autentikasi</span>
    </header>
    <div class="fixed top-[72px] sm:top-[68px] left-0 right-0 h-[3px] z-40 opacity-90" style="background: linear-gradient(90deg, #FFF2CA, #56EFC5, #82EDEC, #92C9FF, #A29BFE);"></div>

    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-24 relative z-10 w-full mt-8 sm:mt-0">
        <div class="w-full max-w-[420px] bg-white/80 border border-[#2E2A47]/[0.08] backdrop-blur-[14px] p-6 sm:p-10 rounded-[28px] shadow-[0_20px_50px_-30px_rgba(122,109,224,0.35)] mx-auto">
            
            <div class="text-center mb-6 sm:mb-8">
                <span class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-[#726D8C] block mb-1 font-mono">Sistem SKS Terpadu</span>
                <h1 class="text-2xl sm:text-3xl font-display font-semibold tracking-tight text-[#2E2A47]">Masuk ke akun</h1>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold tracking-wider uppercase text-[#726D8C] mb-1.5 font-mono">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full bg-white/60 border border-[#2E2A47]/10 focus:border-[#4C86E0] focus:bg-white rounded-xl px-4 py-3.5 text-sm text-[#2E2A47] placeholder-[#726D8C]/50 outline-none transition-all duration-200"
                        placeholder="nama@kampus.ac.id"
                        autofocus
                    >
                    @error('email') 
                        <span class="text-[#FF3B30] text-xs font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-wider uppercase text-[#726D8C] mb-1.5 font-mono">Kata Sandi</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full bg-white/60 border border-[#2E2A47]/10 focus:border-[#4C86E0] focus:bg-white rounded-xl px-4 py-3.5 text-sm text-[#2E2A47] placeholder-[#726D8C]/50 outline-none transition-all duration-200"
                        placeholder="••••••••••••"
                    >
                    @error('password') 
                        <span class="text-[#FF3B30] text-xs font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-[#92C9FF] to-[#A29BFE] text-[#2E2A47] py-3.5 rounded-xl font-display font-semibold text-sm tracking-tight hover:brightness-105 active:scale-[0.98] transition-all duration-200 shadow-[0_10px_30px_-12px_rgba(122,109,224,0.6)] flex items-center justify-center gap-2 group"
                    >
                        <span>Masuk Sekarang</span>
                        <svg class="w-4 h-4 text-[#2E2A47]/70 group-hover:text-[#2E2A47] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

        </div>
    </main>

    <footer class="py-6 px-4 text-center text-[10px] sm:text-xs text-[#726D8C] select-none relative z-10 font-mono">
        Universitas Negeri Malang &bull; Dilindungi Hak Cipta.
    </footer>

</div>
@endsection