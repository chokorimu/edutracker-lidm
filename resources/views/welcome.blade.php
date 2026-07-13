<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset("images/logo.png") }}">
    <title>edutrack daily - Kuliah seimbang, tanpa burnout.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FFFBF2',
                        paper: '#FFFFFF',
                        ink: '#2E2A47',
                        mutedink: '#726D8C',
                        yellow: '#FFF2CA',
                        greentop: '#56EFC5',
                        greenbot: '#82EDEC',
                        blue: '#92C9FF',
                        purple: '#A29BFE',
                        deepGold: '#D6A32E',
                        deepMint: '#12AE8E',
                        deepAqua: '#22A6BE',
                        deepBlue: '#4C86E0',
                        deepPurple: '#7A6DE0',
                        statusGreen: '#34C759',
                        statusOrange: '#FF9500',
                        statusRed: '#FF3B30',
                        statusPurple: '#5856D6',
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        body: ['Inter', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        :root { --spectrum: linear-gradient(90deg, #FFF2CA, #56EFC5, #82EDEC, #92C9FF, #A29BFE); }
        body { background: #FFFBF2; letter-spacing: -0.01em; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ---- Ambient pastel blobs ---- */
        .blob { position: fixed; border-radius: 9999px; filter: blur(80px); pointer-events: none; z-index: 0; opacity: 0.55; }
        .blob-a { width: 520px; height: 520px; background: #FFF2CA; top: -160px; left: -140px; animation: driftA 24s ease-in-out infinite; }
        .blob-b { width: 480px; height: 480px; background: #92C9FF; bottom: -180px; right: -120px; animation: driftB 28s ease-in-out infinite; }
        .blob-c { width: 420px; height: 420px; background: #A29BFE; top: 38%; left: 58%; animation: driftC 20s ease-in-out infinite; opacity: 0.4; }
        .blob-d { width: 380px; height: 380px; background: #82EDEC; top: 55%; left: 5%; animation: driftB 30s ease-in-out infinite reverse; opacity: 0.4; }
        @keyframes driftA { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(50px,70px) scale(1.1); } }
        @keyframes driftB { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-60px,-40px) scale(1.06); } }
        @keyframes driftC { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,50px) scale(0.92); } }

        .grain { position: fixed; inset: 0; z-index: 1; pointer-events: none; opacity: 0.02; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }

        /* ---- Signature spectrum bar ---- */
        .spectrum-line { background: var(--spectrum); background-size: 300% 100%; animation: spectrumShift 7s linear infinite; }
        @keyframes spectrumShift { 0% { background-position: 0% 50%; } 100% { background-position: 300% 50%; } }

        /* ---- Gradient headline text (deepened for legibility) ---- */
        .grad-text { background: linear-gradient(100deg, #D6A32E 0%, #12AE8E 33%, #4C86E0 66%, #7A6DE0 100%); background-size: 260% 260%; -webkit-background-clip: text; background-clip: text; color: transparent; animation: gradFlow 10s ease-in-out infinite; }
        @keyframes gradFlow { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

        /* ---- Cards ---- */
        .card { background: rgba(255,255,255,0.82); border: 1px solid rgba(46,42,71,0.08); backdrop-filter: blur(14px); box-shadow: 0 20px 50px -30px rgba(122,109,224,0.35); }
        .card:hover { border-color: rgba(146,201,255,0.55); box-shadow: 0 24px 60px -26px rgba(86,239,197,0.4); }

        /* ---- Decorative mesh panel (replaces photography) ---- */
        .mesh-panel { position: relative; overflow: hidden; border-radius: 24px; background: linear-gradient(135deg, #FFF2CA, #56EFC5, #82EDEC, #92C9FF, #A29BFE); background-size: 320% 320%; animation: meshShift 14s ease-in-out infinite; }
        .mesh-panel::before { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.18); }
        .mesh-panel .orb { position: absolute; border-radius: 9999px; background: rgba(255,255,255,0.4); filter: blur(18px); }
        @keyframes meshShift { 0%,100% { background-position: 0% 40%; } 50% { background-position: 100% 60%; } }

        /* ---- Slide transitions ---- */
        .slide-card { position: absolute; inset: 0; opacity: 0; transform: translateY(24px) scale(0.985); pointer-events: none; transition: opacity 0.7s cubic-bezier(0.22,1,0.36,1), transform 0.7s cubic-bezier(0.22,1,0.36,1); }
        .slide-card.active { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

        .reveal { opacity: 0; transform: translateY(18px); }
        .slide-card.active .reveal { animation: fadeUp 0.7s cubic-bezier(0.22,1,0.36,1) forwards; }
        .slide-card.active .r1 { animation-delay: .05s; } .slide-card.active .r2 { animation-delay: .12s; }
        .slide-card.active .r3 { animation-delay: .19s; } .slide-card.active .r4 { animation-delay: .26s; }
        .slide-card.active .r5 { animation-delay: .33s; } .slide-card.active .r6 { animation-delay: .40s; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .tilt { transition: transform 0.25s ease-out; transform-style: preserve-3d; }

        @keyframes swipeHint { 0%,100% { transform: translateX(0); opacity: .6; } 50% { transform: translateX(6px); opacity: 1; } }
        .swipe-icon { animation: swipeHint 1.6s ease-in-out infinite; }

        ::selection { background: #A29BFE; color: white; }
    </style>
</head>
<body class="text-ink h-screen overflow-hidden flex flex-col justify-between antialiased font-body relative">

    <div class="blob blob-a"></div>
    <div class="blob blob-b"></div>
    <div class="blob blob-c"></div>
    <div class="blob blob-d"></div>
    <div class="grain"></div>

    <header class="w-full bg-cream/70 backdrop-blur-xl border-b border-ink/[0.06] px-6 md:px-16 py-5 flex justify-between items-center z-50 fixed top-0 left-0 right-0">
        <div class="flex items-center gap-3">
            <x-title/>
        </div>
        <a href="/login" class="relative bg-gradient-to-r from-blue to-purple text-ink px-5 py-2 rounded-full text-xs font-semibold tracking-tight hover:brightness-105 transition-all duration-300 active:scale-95 shadow-[0_10px_30px_-12px_rgba(122,109,224,0.6)] font-display">
            Masuk
        </a>
    </header>
    <div class="fixed top-[68px] left-0 right-0 h-[3px] spectrum-line z-40 opacity-90"></div>

    <button onclick="prevSlide()" aria-label="Sebelumnya" class="hidden md:flex fixed left-6 top-1/2 -translate-y-1/2 z-50 card text-ink w-11 h-11 rounded-full items-center justify-center hover:bg-white active:scale-95 transition-all group">
        <svg class="w-4 h-4 text-mutedink group-hover:text-ink transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <button onclick="nextSlide()" aria-label="Selanjutnya" class="hidden md:flex fixed right-6 top-1/2 -translate-y-1/2 z-50 card text-ink w-11 h-11 rounded-full items-center justify-center hover:bg-white active:scale-95 transition-all group">
        <svg class="w-4 h-4 text-mutedink group-hover:text-ink transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    <main id="interactiveZone" class="flex-1 w-full max-w-6xl mx-auto pt-28 pb-24 px-6 relative h-full z-10">
        <div class="relative w-full h-full">

            <!-- SLIDE 1 - RINGKASAN -->
            <div class="slide-card active flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-6 space-y-5">
                        <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepBlue inline-flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue animate-pulse"></span>Ringkasan Platform</span>
                        <h1 class="reveal r2 text-4xl md:text-6xl font-display font-semibold tracking-tight leading-[1.1]">Manajemen beban <span class="grad-text">akademik,</span><br>untuk mahasiswa.</h1>
                        <p class="reveal r3 text-base text-mutedink max-w-md font-normal leading-relaxed">edutrack daily memonitor beban tugas harian - mengintegrasikan data mahasiswa, dosen, dan program studi dalam satu sistem pemantauan.</p>
                    </div>
                    <div class="md:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="reveal r3 tilt card p-6 rounded-[24px] space-y-2">
                            <h3 class="text-3xl font-display font-semibold tracking-tight text-deepPurple count-up" data-target="51.5" data-suffix="%">0%</h3>
                            <p class="text-xs font-bold uppercase text-ink font-mono">Tugas Tabrakan</p>
                            <p class="text-xs text-mutedink leading-relaxed">Lebih dari separuh mahasiswa mengalami jadwal tenggat tugas yang menumpuk di minggu yang sama tanpa koordinasi antar mata kuliah.</p>
                        </div>
                        <div class="reveal r4 tilt card p-6 rounded-[24px] space-y-2 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-yellow/40 via-transparent to-blue/30"></div>
                            <div class="relative z-10">
                                <h3 class="text-3xl font-display font-semibold tracking-tight text-ink">Statis</h3>
                                <p class="text-xs font-bold uppercase text-ink mt-2 font-mono">SIAKAD Konvensional</p>
                                <p class="text-xs text-mutedink leading-relaxed mt-1">Sistem akademik konvensional umumnya hanya mencatat hasil akhir, bukan memantau beban belajar berjalan secara realtime.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2 - URGENSI -->
            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-5 space-y-4">
                        <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepMint inline-flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-greentop animate-pulse"></span>Urgensi</span>
                        <h2 class="reveal r2 text-4xl md:text-5xl font-display font-semibold tracking-tight leading-tight">Pantau beban, tingkatkan <span class="grad-text">fokus.</span></h2>
                        <p class="reveal r3 text-sm text-mutedink leading-relaxed">Kelelahan akademik sering bersumber dari manajemen beban yang kurang terpantau. Kami membantu instansi pendidikan memastikan proses belajar mengajar tetap terukur dan seimbang.</p>
                    </div>
                    <div class="md:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="reveal r2 tilt card p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-deepMint block font-mono">16–20 SKS</span>
                            <p class="text-xs text-mutedink leading-relaxed">Rentang SKS optimal untuk menjaga efektivitas penyerapan materi pelajaran mahasiswa per semester.</p>
                        </div>
                        <div class="reveal r3 tilt card p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-deepGold block font-mono">5 Solusi Inti</span>
                            <p class="text-xs text-mutedink leading-relaxed">Manajemen tenggat tugas, deteksi beban berlebih, dashboard analitik, sinkronisasi data, dan monitoring lintas prodi.</p>
                        </div>
                        <div class="reveal r4 tilt card p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-deepBlue block font-mono">Satu Ekosistem</span>
                            <p class="text-xs text-mutedink leading-relaxed">Terkoneksi langsung antara dashboard mahasiswa, pemantauan dosen wali, dan laporan rekapitulasi program studi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3 - METODE -->
            <div class="slide-card flex items-center">
                <div class="w-full space-y-8 max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8 items-end">
                        <div class="md:col-span-7 space-y-2">
                            <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepPurple block">Metode</span>
                            <h2 class="reveal r2 text-4xl md:text-5xl font-display font-semibold tracking-tight">Didesain berdasarkan <span class="grad-text">data.</span></h2>
                        </div>
                        <div class="md:col-span-5">
                            <p class="reveal r3 text-xs md:text-sm text-mutedink leading-relaxed">Dikembangkan dengan pendekatan Design Thinking untuk memastikan setiap fitur secara efektif menjawab permasalahan nyata dalam administrasi tugas kuliah.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        <div class="reveal r2 tilt card p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold font-mono text-deepMint">01 - Empathize</div>
                            <p class="text-[11px] text-mutedink leading-normal">Survei kebiasaan studi dan masalah manajemen waktu mahasiswa.</p>
                        </div>
                        <div class="reveal r3 tilt card p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold font-mono text-deepAqua">02 - Define</div>
                            <p class="text-[11px] text-mutedink leading-normal">Identifikasi masalah utama pada jadwal tenggat tugas yang tumpang tindih.</p>
                        </div>
                        <div class="reveal r4 tilt card p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold font-mono text-deepBlue">03 - Ideate</div>
                            <p class="text-[11px] text-mutedink leading-normal">Penyusunan solusi berbasis grafik dan indikator peringatan beban SKS.</p>
                        </div>
                        <div class="reveal r5 tilt card p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold font-mono text-deepGold">04 - Prototype</div>
                            <p class="text-[11px] text-mutedink leading-normal">Pembuatan antarmuka dashboard untuk mahasiswa, dosen, dan operator.</p>
                        </div>
                        <div class="reveal r6 tilt card p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold font-mono text-deepPurple">05 - Test</div>
                            <p class="text-[11px] text-mutedink leading-normal">Uji coba interaktif dan evaluasi UI/UX bersama pengguna akhir.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 4 - SISTEM -->
            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-4 space-y-4">
                        <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepAqua">Sistem</span>
                        <h2 class="reveal r2 text-4xl font-display font-semibold tracking-tight leading-tight">Berbasis web.<br><span class="grad-text">Mudah diakses.</span></h2>
                        <p class="reveal r3 text-xs text-mutedink leading-relaxed">Dapat diakses melalui peramban di perangkat apapun. Sistem terintegrasi dengan arsitektur data Universitas Negeri Malang via API.</p>
                    </div>
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="reveal r2 tilt card p-6 rounded-[24px] space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-deepMint font-mono">Learning Analytics</h4>
                            <p class="text-xs text-mutedink leading-relaxed">Agregasi data histori tugas akademik, diproses untuk memetakan tingkat kesibukan ke dalam indikator visual 4 tingkat.</p>
                        </div>
                        <div class="reveal r3 tilt card p-6 rounded-[24px] space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-deepGold font-mono">Peringatan Dini</h4>
                            <p class="text-xs text-mutedink leading-relaxed">Sistem alert otomatis untuk memberitahu mahasiswa dan dosen wali ketika ambang batas maksimal beban terlampaui.</p>
                        </div>
                        <div class="reveal r4 tilt card p-6 rounded-[24px] space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-deepPurple font-mono">Visualisasi Intuitif</h4>
                            <p class="text-xs text-mutedink leading-relaxed">Representasi numerik diolah menjadi grafik progres, heat map kalender, dan kartu indikator metrik mingguan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 5 - FITUR (signature) -->
            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-5 space-y-4">
                        <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepGold">Fitur</span>
                        <h2 class="reveal r2 text-4xl font-display font-semibold tracking-tight leading-tight">Pemetaan kepadatan jadwal <span class="grad-text">tugas mingguan.</span></h2>
                        <p class="reveal r3 text-xs text-mutedink leading-relaxed">Dosen dapat mengecek jadwal penugasan dari mata kuliah lain sebelum menetapkan tenggat. Indikator kapasitas beban menghindarkan tabrakan jadwal deadline.</p>
                    </div>
                    <div class="md:col-span-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        <div class="reveal r2 tilt card p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full block" style="background:#34C759; box-shadow:0 0 14px 2px rgba(52,199,89,0.5);"></span>
                            <div>
                                <span class="text-xs font-bold block text-ink font-mono">Hijau</span>
                                <span class="text-[11px] text-mutedink block mt-0.5">Beban Ringan (Aman)</span>
                            </div>
                        </div>
                        <div class="reveal r3 tilt card p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full block" style="background:#FF9500; box-shadow:0 0 14px 2px rgba(255,149,0,0.5);"></span>
                            <div>
                                <span class="text-xs font-bold block text-ink font-mono">Oranye</span>
                                <span class="text-[11px] text-mutedink block mt-0.5">Normal (Pemantauan)</span>
                            </div>
                        </div>
                        <div class="reveal r4 tilt card p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full block" style="background:#FF3B30; box-shadow:0 0 14px 2px rgba(255,59,48,0.5);"></span>
                            <div>
                                <span class="text-xs font-bold block text-ink font-mono">Merah</span>
                                <span class="text-[11px] text-mutedink block mt-0.5">Berat (Antisipasi)</span>
                            </div>
                        </div>
                        <div class="reveal r5 tilt card p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full block" style="background:#5856D6; box-shadow:0 0 14px 2px rgba(88,86,214,0.5);"></span>
                            <div>
                                <span class="text-xs font-bold block text-deepPurple font-mono">Ungu</span>
                                <span class="text-[11px] text-mutedink block mt-0.5">Overload (Kritis)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 6 - PENGUJIAN -->
            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-4 space-y-3">
                        <span class="reveal r1 text-[11px] font-mono font-semibold tracking-[0.2em] uppercase text-deepMint">Pengujian</span>
                        <h2 class="reveal r2 text-3xl md:text-5xl font-display font-semibold tracking-tight leading-tight">Telah melewati <span class="grad-text">uji validasi.</span></h2>
                        <p class="reveal r3 text-sm md:text-base text-mutedink leading-relaxed mt-2 md:mt-4">Divalidasi oleh pakar media dan kurikulum, serta diuji coba fungsionalitasnya dengan responden mahasiswa dan dosen wali aktif.</p>
                    </div>
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
                        <div class="reveal r2 tilt card p-6 md:p-8 rounded-[24px] md:rounded-[32px] space-y-3 md:space-y-4">
                            <span class="text-sm md:text-base font-bold block text-ink font-mono">White & Black Box</span>
                            <p class="text-xs md:text-sm text-mutedink leading-relaxed">Pengujian struktur kode internal (White Box) dan fungsionalitas input-output antarmuka pengguna (Black Box).</p>
                        </div>
                        <div class="reveal r3 tilt card p-6 md:p-8 rounded-[24px] md:rounded-[32px] space-y-3 md:space-y-4">
                            <span class="text-sm md:text-base font-bold block text-ink font-mono">Skala SUS Baku</span>
                            <p class="text-xs md:text-sm text-mutedink leading-relaxed">Evaluasi tingkat kenyamanan penggunaan (usability) dengan parameter kuantitatif System Usability Scale.</p>
                        </div>
                        <div class="reveal r4 tilt card p-6 md:p-8 rounded-[24px] md:rounded-[32px] space-y-3 md:space-y-4">
                            <span class="text-sm md:text-base font-bold block text-ink font-mono">6 Aspek Media</span>
                            <p class="text-xs md:text-sm text-mutedink leading-relaxed">Penilaian dari segi desain antarmuka, fungsionalitas, performa muat, keandalan informasi, dan efektivitas navigasi.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="flex md:hidden fixed bottom-24 left-1/2 -translate-x-1/2 z-40 items-center gap-1.5 text-[10px] font-mono text-mutedink/80 bg-white/70 backdrop-blur px-3 py-1.5 rounded-full border border-ink/[0.06]">
        Geser untuk lanjut
        <svg class="w-3 h-3 swipe-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
    </div>

    <footer class="w-full bg-cream/60 backdrop-blur-sm py-6 px-6 md:px-16 flex justify-between items-center z-50 fixed bottom-0 left-0 right-0 border-t border-ink/[0.06]">
        <span class="text-[10px] font-semibold tracking-widest text-mutedink/70 uppercase hidden sm:block select-none font-mono">edutrack daily platform engine</span>
        <div class="flex space-x-2 mx-auto sm:mx-0" id="indicatorBar">
            <button onclick="slideActive(0)" class="w-2 h-2 rounded-full bg-purple transition-all duration-500"></button>
            <button onclick="slideActive(1)" class="w-2 h-2 rounded-full bg-ink/15 transition-all duration-500"></button>
            <button onclick="slideActive(2)" class="w-2 h-2 rounded-full bg-ink/15 transition-all duration-500"></button>
            <button onclick="slideActive(3)" class="w-2 h-2 rounded-full bg-ink/15 transition-all duration-500"></button>
            <button onclick="slideActive(4)" class="w-2 h-2 rounded-full bg-ink/15 transition-all duration-500"></button>
            <button onclick="slideActive(5)" class="w-2 h-2 rounded-full bg-ink/15 transition-all duration-500"></button>
        </div>
    </footer>

    <script>
        let indexActive = 0;
        const maxSlides = 6;
        const slides = document.getElementsByClassName('slide-card');
        const dots = document.getElementById('indicatorBar').getElementsByTagName('button');

        let automationTimer;
        const idleDelay = 8000;

        function runCountUps(container) {
            container.querySelectorAll('.count-up').forEach(el => {
                if (el.dataset.done) return;
                el.dataset.done = '1';
                const target = parseFloat(el.dataset.target);
                const suffix = el.dataset.suffix || '';
                const duration = 1100;
                const start = performance.now();
                function tick(now) {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    const val = (target * eased).toFixed(target % 1 !== 0 ? 1 : 0);
                    el.textContent = val + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });
        }

        function renderCarousel() {
            for (let i = 0; i < slides.length; i++) {
                if (i === indexActive) {
                    slides[i].classList.add('active');
                    dots[i].classList.remove('bg-ink/15');
                    dots[i].classList.add('bg-purple', 'w-8');
                    runCountUps(slides[i]);
                } else {
                    slides[i].classList.remove('active');
                    dots[i].classList.remove('bg-purple', 'w-8');
                    dots[i].classList.add('bg-ink/15');
                }
            }
        }

        function nextSlide() {
            indexActive = (indexActive + 1) % maxSlides;
            renderCarousel();
        }

        function prevSlide() {
            indexActive = (indexActive - 1 + maxSlides) % maxSlides;
            renderCarousel();
        }

        function slideActive(targetIndex) {
            indexActive = targetIndex;
            renderCarousel();
            keepReadingReset();
        }

        function startAutomation() {
            clearTimeout(automationTimer);
            automationTimer = setTimeout(() => {
                nextSlide();
                startAutomation();
            }, idleDelay);
        }

        function keepReadingReset() {
            clearTimeout(automationTimer);
            startAutomation();
        }

        const interactiveCanvas = document.getElementById('interactiveZone');
        window.addEventListener('mousemove', keepReadingReset);
        window.addEventListener('keydown', keepReadingReset);
        window.addEventListener('click', keepReadingReset);
        interactiveCanvas.addEventListener('scroll', keepReadingReset, { passive: true });

        /* Tilt effect on cards (desktop pointer only) */
        document.querySelectorAll('.tilt').forEach(card => {
            card.addEventListener('mousemove', e => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform = `perspective(600px) rotateX(${(-y * 6).toFixed(2)}deg) rotateY(${(x * 6).toFixed(2)}deg) translateZ(4px)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(600px) rotateX(0) rotateY(0) translateZ(0)';
            });
        });

        /* Desktop: arrow keys move slides */
        window.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight') nextSlide();
            if (e.key === 'ArrowLeft') prevSlide();
        });

        /* Mobile: swipe left/right */
        let startX = 0;
        let endX = 0;

        interactiveCanvas.addEventListener('touchstart', e => {
            startX = e.changedTouches[0].screenX;
            keepReadingReset();
        }, { passive: true });

        interactiveCanvas.addEventListener('touchend', e => {
            endX = e.changedTouches[0].screenX;
            evaluateSwipe();
            keepReadingReset();
        }, { passive: true });

        function evaluateSwipe() {
            const threshold = 50;
            if (endX < startX - threshold) { nextSlide(); }
            if (endX > startX + threshold) { prevSlide(); }
        }

        renderCarousel();
        startAutomation();
    </script>
</body>
</html>