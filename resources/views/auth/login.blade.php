@extends('layouts.guest')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Entrar no Sistema</h2>
        <p class="text-sm text-slate-500 mt-1">Insira suas credenciais corporativas para acessar o painel da clínica.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 border border-rose-200">
            <div class="font-semibold mb-1">Atenção:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
            <div class="mt-1">
                <input id="email" name="email" type="email" autocomplete="email" required
                       value="{{ old('email', 'admin@clinicafabris.com.br') }}"
                       class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 shadow-sm placeholder-slate-400 focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600 sm:text-sm">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
            </div>
            <div class="mt-1">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       value="admin123"
                       class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 shadow-sm placeholder-slate-400 focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600 sm:text-sm">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                <label for="remember" class="ml-2 block text-sm text-slate-600">Lembrar-me</label>
            </div>
        </div>

        <div>
            <button type="submit"
                    class="flex w-full justify-center items-center rounded-xl bg-teal-600 py-2.5 px-4 text-sm font-semibold text-white shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors">
                Acessar Plataforma
            </button>
        </div>
    </form>

    <!-- Atalhos de demonstração para facilitar testes do usuário -->
    <div class="mt-8 pt-6 border-t border-slate-200" x-data>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Acesso Rápido por Perfil (Ambiente de Testes)</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" @click="document.getElementById('email').value='admin@clinicafabris.com.br'; document.getElementById('password').value='admin123';"
                    class="p-2 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 text-left border border-teal-200 transition">
                <span class="font-bold block">Proprietária</span>
                admin@clinicafabris.com.br
            </button>
            <button type="button" @click="document.getElementById('email').value='recepcao@clinicafabris.com.br'; document.getElementById('password').value='recepcao123';"
                    class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 text-left border border-blue-200 transition">
                <span class="font-bold block">Recepção</span>
                recepcao@clinicafabris.com.br
            </button>
            <button type="button" @click="document.getElementById('email').value='fisioterapeuta@clinicafabris.com.br'; document.getElementById('password').value='fisio123';"
                    class="p-2 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-800 text-left border border-purple-200 transition">
                <span class="font-bold block">Fisioterapeuta</span>
                fisioterapeuta@clinicafabris.com.br
            </button>
            <button type="button" @click="document.getElementById('email').value='financeiro@clinicafabris.com.br'; document.getElementById('password').value='financeiro123';"
                    class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-left border border-emerald-200 transition">
                <span class="font-bold block">Financeiro</span>
                financeiro@clinicafabris.com.br
            </button>
        </div>
    </div>
@endsection
