<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Clínica Fabris') }} - Gestão Fisioterapêutica</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-teal-900 via-slate-900 to-emerald-950 text-slate-800 antialiased">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-teal-500/20 text-teal-300 ring-1 ring-teal-500/40 shadow-xl mb-4">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-white font-sans">{{ config('app.name', 'Clínica Fabris') }}</h1>
        <p class="mt-2 text-sm text-teal-200/80">Sistema de Gestão & Prontuário Fisioterapêutico</p>
    </div>

    <div class="w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-100">
            @yield('content')
        </div>
        <p class="mt-6 text-center text-xs text-teal-200/50">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
