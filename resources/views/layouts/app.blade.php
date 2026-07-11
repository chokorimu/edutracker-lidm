<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset("images/logo.png") }}">
    <title>edutrack daily SKS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        body: ['Inter', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        /* ---- Global Ambient pastel blobs untuk Dashboard (Kanan Saja) ---- */
        .global-blob { position: fixed; border-radius: 9999px; filter: blur(80px); pointer-events: none; z-index: 0; opacity: 0.35; }
        .global-blob-1 { width: 480px; height: 480px; background: #92C9FF; top: -10%; right: -5%; animation: globalDrift 28s ease-in-out infinite; }
        .global-blob-2 { width: 420px; height: 420px; background: #A29BFE; bottom: -15%; right: 10%; animation: globalDriftReverse 20s ease-in-out infinite; opacity: 0.3; }
        .global-blob-3 { width: 380px; height: 380px; background: #82EDEC; top: 40%; right: -10%; animation: globalDrift 30s ease-in-out infinite reverse; opacity: 0.25; }
        @keyframes globalDrift { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-40px,30px) scale(1.05); } }
        @keyframes globalDriftReverse { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-40px) scale(0.95); } }
        .global-grain { position: fixed; inset: 0; z-index: 1; pointer-events: none; opacity: 0.015; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }
    </style>
</head>
<body class="bg-cream text-ink font-body relative">
    @if(!request()->routeIs('login'))
        <div class="global-blob global-blob-1"></div>
        <div class="global-blob global-blob-2"></div>
        <div class="global-blob global-blob-3"></div>
        <div class="global-grain"></div>
    @endif
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>
