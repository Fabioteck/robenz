<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e40af">
    <title>@yield('title', 'RoBenz') – Prezzi carburanti a Rovigo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Animazione fade-in per le card */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .25s ease both; }
        .fade-up:nth-child(1)  { animation-delay: .03s }
        .fade-up:nth-child(2)  { animation-delay: .06s }
        .fade-up:nth-child(3)  { animation-delay: .09s }
        .fade-up:nth-child(4)  { animation-delay: .12s }
        .fade-up:nth-child(5)  { animation-delay: .15s }
        .fade-up:nth-child(n+6){ animation-delay: .18s }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased">

    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-brand-900 text-white shadow-lg">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('stations.index') }}" class="flex items-center gap-2 font-bold text-lg tracking-tight">
                <span class="text-2xl">⛽</span>
                <span>Rovigo<span class="text-blue-300">Carburanti</span></span>
            </a>
            <span class="text-xs text-blue-200 hidden sm:block">Dati MIMIT Open Data</span>
        </div>
    </header>

    {{-- Contenuto principale --}}
    <main class="max-w-2xl mx-auto px-4 py-6 pb-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-slate-200 bg-white mt-8">
        <div class="max-w-2xl mx-auto px-4 py-4 text-center text-xs text-slate-400 space-y-1">
            <p>Dati aggiornati quotidianamente · Fonte: <strong>MIMIT Open Data</strong></p>
            <p>Progetto indipendente e libero (<a href="https://github.com/fabioteck/robenz" class="text-blue-500 hover:underline">GitHub</a>), 
                sviluppato da <a href="https://www.fabioteck.it" class="text-blue-500 hover:underline">Fabio Frigeri</a>per la città di Rovigo</p>
        </div>
    </footer>

</body>
</html>
