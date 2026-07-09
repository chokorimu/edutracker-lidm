<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset("images/logo.png") }}">
    <title>edutrack daily — Kuliah seimbang, tanpa burnout.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bone: {
                            light: '#FBFBF9',
                            DEFAULT: '#F5F5F7',
                            dark: '#E8E8ED',
                        },
                        appleDark: '#1D1D1F',
                        appleMuted: '#86868B',
                        appleGreen: '#34C759',
                        appleOrange: '#FF9500',
                        appleRed: '#FF3B30',
                    },
                    fontFamily: {
                        sans: ['SF Pro Display', '-apple-system', 'BlinkMacSystemFont', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #FBFBF9; letter-spacing: -0.015em; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Premium Apple Transition Style */
        .slide-card {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateY(16px) scale(0.99);
            pointer-events: none;
            transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), 
                        transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .slide-card.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
    </style>
</head>
<body class="text-appleDark h-screen overflow-hidden flex flex-col justify-between antialiased selection:bg-appleDark selection:text-white">

    <header class="w-full bg-bone-light/80 backdrop-blur-xl border-b border-bone-dark/40 px-6 md:px-16 py-5 flex justify-between items-center z-50 fixed top-0 left-0 right-0">
        <x-title/>
        <a href="/login" class="bg-appleDark text-white px-5 py-2 rounded-full text-xs font-medium tracking-tight hover:bg-appleDark/90 transition-all duration-300 active:scale-95 shadow-sm">
            Masuk
        </a>
    </header>

    <button onclick="prevSlide()" class="hidden md:flex fixed left-6 top-1/2 -translate-y-1/2 z-50 bg-white/80 backdrop-blur-md border border-bone-dark text-appleDark w-11 h-11 rounded-full items-center justify-center shadow-sm hover:bg-white active:scale-95 transition-all group">
        <svg class="w-4 h-4 text-appleDark/70 group-hover:text-appleDark transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <button onclick="nextSlide()" class="hidden md:flex fixed right-6 top-1/2 -translate-y-1/2 z-50 bg-white/80 backdrop-blur-md border border-bone-dark text-appleDark w-11 h-11 rounded-full items-center justify-center shadow-sm hover:bg-white active:scale-95 transition-all group">
        <svg class="w-4 h-4 text-appleDark/70 group-hover:text-appleDark transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    <main id="interactiveZone" class="flex-1 w-full max-w-6xl mx-auto pt-28 pb-24 px-6 relative h-full">
        <div class="relative w-full h-full">
            
            <div class="slide-card active flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-6 space-y-4">
                        <span class="text-xs font-bold tracking-widest uppercase text-appleMuted">01 / Ringkasan</span>
                        <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-appleDark leading-[1.1]">Kuliah <span class="text-green-500">seimbang.</span><br>Tanpa <span class="text-red-500">burnout.</span></h1>
                        <p class="text-base text-appleMuted max-w-md font-normal leading-relaxed">edutrack daily memantau beban studi secara real-time. Menghubungkan mahasiswa, dosen, dan prodi dalam satu ekosistem berbasis data.</p>
                    </div>
                    <div class="md:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white border border-bone-dark p-6 rounded-[24px] space-y-2">
                            <h3 class="text-3xl font-bold tracking-tight text-appleRed">51,5%</h3>
                            <p class="text-xs font-bold uppercase text-appleDark">Tugas Tabrakan</p>
                            <p class="text-xs text-appleMuted leading-relaxed">Mayoritas mahasiswa menghadapi lebih dari 3 tugas besar di minggu yang sama. Produktivitas menurun drastis.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-6 rounded-[24px] space-y-2">
                            <h3 class="text-3xl font-bold tracking-tight text-appleDark">Statis</h3>
                            <p class="text-xs font-bold uppercase text-appleDark">SIAKAD Konvensional</p>
                            <p class="text-xs text-appleMuted leading-relaxed">Sistem kampus hari ini hanya merekam nilai masa lalu. Bukan memprediksi atau mencegah stres akademik.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-5 space-y-4">
                        <span class="text-xs font-bold tracking-widest uppercase text-appleMuted">02 / Urgensi</span>
                        <h1 class="text-4xl md:text-5xl font-bold tracking-tight leading-tight">Bertahan, bukan sekadar berjalan.</h1>
                        <p class="text-sm text-appleMuted leading-relaxed">Ratusan ribu mahasiswa putus kuliah setiap tahun karena beban studi yang tak terkelola. Selaras dengan target SDGs ke-4, kami mendefinisikan ulang kualitas pendidikan lewat kesejahteraan mental.</p>
                    </div>
                    <div class="md:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white border border-bone-dark p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-appleDark block">16–20 SKS</span>
                            <p class="text-xs text-appleMuted leading-relaxed">Titik optimal. Produktivitas tertinggi dicapai saat beban akademik berada di batas aman.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-appleDark block">5 Solusi Inti</span>
                            <p class="text-xs text-appleMuted leading-relaxed">Transparansi tenggat waktu, prediksi risiko, evaluasi prodi, dan integrasi mulus.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-6 rounded-[24px] space-y-2">
                            <span class="text-xs font-bold text-appleDark block">Satu Ekosistem</span>
                            <p class="text-xs text-appleMuted leading-relaxed">Mahasiswa terjaga, dosen teredukasi, prodi mendapatkan data akurat untuk akreditasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-card flex items-center">
                <div class="w-full space-y-8 max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8 items-end">
                        <div class="md:col-span-7 space-y-2">
                            <span class="text-xs font-bold tracking-widest uppercase text-appleMuted block">03 / Metode</span>
                            <h2 class="text-4xl md:text-5xl font-bold tracking-tight text-appleDark">Didesain untuk manusia.</h2>
                        </div>
                        <div class="md:col-span-5">
                            <p class="text-xs md:text-sm text-appleMuted leading-relaxed">Kami tidak merancang dari balik meja. Pendekatan Design Thinking memastikan setiap fitur lahir dari validasi langsung bersama mahasiswa.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5">
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold text-appleDark">1. Empathize</div>
                            <p class="text-[11px] text-appleMuted leading-normal">Mendengar langsung keresahan terdalam.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold text-appleDark">2. Define</div>
                            <p class="text-[11px] text-appleMuted leading-normal">Memetakan akar masalah: penumpukan deadline.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold text-appleDark">3. Ideate</div>
                            <p class="text-[11px] text-appleMuted leading-normal">Menyusun prioritas fitur paling berdampak.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold text-appleDark">4. Prototype</div>
                            <p class="text-[11px] text-appleMuted leading-normal">Merealisasikan ide ke dalam bentuk MVP.</p>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-2">
                            <div class="text-xs font-bold text-appleDark">5. Test</div>
                            <p class="text-[11px] text-appleMuted leading-normal">Validasi langsung pada pengguna aktif kampus.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-4 space-y-4">
                        <span class="text-xs font-bold tracking-widest uppercase text-appleMuted">04 / Sistem</span>
                        <h2 class="text-4xl font-bold tracking-tight leading-tight">Berbasis web.<br>Ringan. Instan.</h2>
                        <p class="text-xs text-appleMuted leading-relaxed">Tanpa instalasi, tanpa beban memori. Dapat diakses dari perangkat apa pun, terintegrasi aman dengan API SIAKAD Universitas Negeri Malang.</p>
                    </div>
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="border border-bone-dark p-6 rounded-[24px] space-y-2 bg-white">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-appleDark">Learning Analytics</h4>
                            <p class="text-xs text-appleMuted leading-relaxed">Sistem menganalisis data akademik secara otomatis dan membaginya ke dalam empat kategori visual yang presisi.</p>
                        </div>
                        <div class="border border-bone-dark p-6 rounded-[24px] space-y-2 bg-white">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-appleDark">Peringatan Dini</h4>
                            <p class="text-xs text-appleMuted leading-relaxed">Bekerja di latar belakang. Notifikasi otomatis akan dikirim ke mahasiswa dan dosen wali saat beban studi terdeteksi overload.</p>
                        </div>
                        <div class="border border-bone-dark p-6 rounded-[24px] space-y-2 bg-white">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-appleDark">Visualisasi Intuitif</h4>
                            <p class="text-xs text-appleMuted leading-relaxed">Sederhana dan jelas. Mengganti deretan angka rumit dengan grafik batang, heatmap kalender, dan kartu indikator risiko.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-5 space-y-4">
                        <span class="text-xs font-bold tracking-widest uppercase text-appleMuted">05 / Fitur</span>
                        <h2 class="text-4xl font-bold tracking-tight leading-tight">Melihat kesibukan kelas sebelum memberi tugas.</h2>
                        <p class="text-xs text-appleMuted leading-relaxed">Saat dosen merencanakan tugas baru, sistem menampilkan grafik beban mahasiswa pada minggu tersebut. Jika kapasitas penuh, sistem menyarankan penyesuaian jadwal secara instan.</p>
                    </div>
                    
                    <div class="md:col-span-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full bg-[#34C759] block shadow-sm"></span>
                            <div>
                                <span class="text-xs font-bold block text-appleDark">Hijau</span>
                                <span class="text-[11px] text-appleMuted block mt-0.5">Beban Ringan (Aman)</span>
                            </div>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full bg-[#FF9500] block shadow-sm"></span>
                            <div>
                                <span class="text-xs font-bold block text-appleDark">Oranye</span>
                                <span class="text-[11px] text-appleMuted block mt-0.5">Normal (Pemantauan)</span>
                            </div>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-3">
                            <span class="w-3 h-3 rounded-full bg-[#FF3B30] block shadow-sm"></span>
                            <div>
                                <span class="text-xs font-bold block text-appleDark">Merah</span>
                                <span class="text-[11px] text-appleMuted block mt-0.5">Berat (Antisipasi)</span>
                            </div>
                        </div>
                        <div class="bg-white border border-bone-dark p-5 rounded-[24px] space-y-3 border-purple-200">
                            <span class="w-3 h-3 rounded-full bg-[#5856D6] block shadow-sm"></span>
                            <div>
                                <span class="text-xs font-bold block text-purple-700">Ungu</span>
                                <span class="text-[11px] text-appleMuted block mt-0.5">Overload (Kritis)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-card flex items-center">
                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center max-h-[70vh] overflow-y-auto no-scrollbar py-2">
                    <div class="md:col-span-4 space-y-3">
                        <span class="text-xs font-bold tracking-widest uppercase text-appleMuted">06 / Pengujian</span>
                        <h2 class="text-4xl font-bold tracking-tight leading-tight">Teruji secara ilmiah.</h2>
                        <p class="text-xs text-appleMuted leading-relaxed">Platform ini divalidasi oleh panel ahli tata kelola, TI, dan kurikulum, serta diuji nyata oleh puluhan mahasiswa dan dosen pembimbing.</p>
                    </div>
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white p-6 border border-bone-dark rounded-[24px] space-y-2">
                            <span class="text-xs font-bold block text-appleDark">White & Black Box</span>
                            <p class="text-[11px] text-appleMuted leading-relaxed">Pengujian menyeluruh pada logika internal kode dan fungsionalitas antarmuka tanpa celah eror.</p>
                        </div>
                        <div class="bg-white p-6 border border-bone-dark rounded-[24px] space-y-2">
                            <span class="text-xs font-bold block text-appleDark">Skala SUS Baku</span>
                            <p class="text-[11px] text-appleMuted leading-relaxed">Tingkat kemudahan navigasi diukur menggunakan standar internasional System Usability Scale.</p>
                        </div>
                        <div class="bg-white p-6 border border-bone-dark rounded-[24px] space-y-2">
                            <span class="text-xs font-bold block text-appleDark">6 Aspek Media</span>
                            <p class="text-[11px] text-appleMuted leading-relaxed">Penilaian ketat mencakup aspek visual, kejelasan informasi, kecepatan, akurasi, motivasi, dan inovasi solusi.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="w-full bg-bone-light/40 backdrop-blur-sm py-6 px-6 md:px-16 flex justify-between items-center z-50 fixed bottom-0 left-0 right-0">
        <span class="text-[10px] font-semibold tracking-widest text-appleMuted uppercase hidden sm:block select-none">edutrack daily platform engine</span>
        <div class="flex space-x-2 mx-auto sm:mx-0" id="indicatorBar">
            <button onclick="slideActive(0)" class="w-2 h-2 rounded-full bg-appleDark transition-all duration-500"></button>
            <button onclick="slideActive(1)" class="w-2 h-2 rounded-full bg-bone-dark transition-all duration-500"></button>
            <button onclick="slideActive(2)" class="w-2 h-2 rounded-full bg-bone-dark transition-all duration-500"></button>
            <button onclick="slideActive(3)" class="w-2 h-2 rounded-full bg-bone-dark transition-all duration-500"></button>
            <button onclick="slideActive(4)" class="w-2 h-2 rounded-full bg-bone-dark transition-all duration-500"></button>
            <button onclick="slideActive(5)" class="w-2 h-2 rounded-full bg-bone-dark transition-all duration-500"></button>
        </div>
    </footer>

    <script>
        let indexActive = 0;
        const maxSlides = 6;
        const slides = document.getElementsByClassName('slide-card');
        const dots = document.getElementById('indicatorBar').getElementsByTagName('button');
        
        let automationTimer;
        const idleDelay = 8000;

        function renderCarousel() {
            for (let i = 0; i < slides.length; i++) {
                if (i === indexActive) {
                    slides[i].classList.add('active');
                    dots[i].classList.remove('bg-bone-dark');
                    dots[i].classList.add('bg-appleDark', 'w-8');
                } else {
                    slides[i].classList.remove('active');
                    dots[i].classList.remove('bg-appleDark', 'w-8');
                    dots[i].classList.add('bg-bone-dark');
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
            if (endX < startX - threshold) {
                nextSlide();
            }
            if (endX > startX + threshold) {
                prevSlide();
            }
        }

        renderCarousel();
        startAutomation();
    </script>
</body>
</html>
