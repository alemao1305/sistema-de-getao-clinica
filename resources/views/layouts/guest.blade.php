<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Clínica Fabris') }} - Gestão Fisioterapêutica</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen w-full flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 text-slate-800 antialiased font-sans">
    <div class="w-full max-w-lg my-auto flex flex-col items-center">
        <!-- Logo & Header Centralizado -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-teal-500 text-white shadow-xl shadow-teal-500/25 mb-3">
                <i data-lucide="activity" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-sans">{{ config('app.name', 'Clínica Fabris') }}</h1>
            <p class="mt-1 text-xs sm:text-sm text-teal-300/80 font-medium">Sistema de Gestão & Prontuário Fisioterapêutico</p>
        </div>

        <!-- Card Centralizado -->
        <div class="w-full bg-white py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-slate-100">
            @yield('content')
        </div>

        <!-- Rodapé Centralizado -->
        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Clínica Fabris') }}. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
