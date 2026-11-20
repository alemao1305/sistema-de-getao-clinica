@extends('layouts.guest')

@section('content')
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-slate-900">Entrar no Sistema</h2>
        <p class="text-xs text-slate-500 mt-1">Insira suas credenciais corporativas para acessar o painel da clínica.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 p-3.5 text-xs text-emerald-800 border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-rose-50 p-3.5 text-xs text-rose-800 border border-rose-200">
            <div class="font-semibold mb-1 flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                Atenção ao preencher:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">E-mail Corporativo</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" name="email" type="email" autocomplete="email" required
                       value="{{ old('email', 'admin@clinicafabris.com.br') }}"
                       placeholder="seu.email@clinicafabris.com.br"
                       class="block w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-xs shadow-sm placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold text-slate-700">Senha de Acesso</label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       value="admin123"
                       placeholder="••••••••"
                       class="block w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-xs shadow-sm placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition">
            </div>
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center cursor-pointer">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                <span class="ml-2 text-xs text-slate-600">Lembrar meu acesso neste dispositivo</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full flex justify-center items-center gap-2 rounded-xl bg-teal-600 py-2.5 px-4 text-xs font-bold text-white shadow-md shadow-teal-600/20 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Acessar o Sistema</span>
            </button>
        </div>
    </form>

    <!-- Atalhos de demonstração para facilitar testes do usuário -->
    <div class="mt-8 pt-6 border-t border-slate-100" x-data>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">Acesso Rápido por Perfil (Ambiente de Demonstração)</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" @click="document.getElementById('email').value='admin@clinicafabris.com.br'; document.getElementById('password').value='admin123';"
                    class="p-2.5 rounded-xl bg-teal-50 hover:bg-teal-100/80 text-teal-900 text-left border border-teal-200 transition">
                <span class="font-bold block text-[11px] text-teal-700">Proprietária</span>
                <span class="text-[10px] text-slate-500 block truncate">admin@clinicafabris...</span>
            </button>
            <button type="button" @click="document.getElementById('email').value='recepcao@clinicafabris.com.br'; document.getElementById('password').value='recepcao123';"
                    class="p-2.5 rounded-xl bg-blue-50 hover:bg-blue-100/80 text-blue-900 text-left border border-blue-200 transition">
                <span class="font-bold block text-[11px] text-blue-700">Recepção</span>
                <span class="text-[10px] text-slate-500 block truncate">recepcao@clinicafabris...</span>
            </button>
            <button type="button" @click="document.getElementById('email').value='fisioterapeuta@clinicafabris.com.br'; document.getElementById('password').value='fisio123';"
                    class="p-2.5 rounded-xl bg-purple-50 hover:bg-purple-100/80 text-purple-900 text-left border border-purple-200 transition">
                <span class="font-bold block text-[11px] text-purple-700">Fisioterapeuta</span>
                <span class="text-[10px] text-slate-500 block truncate">fisioterapeuta@clinica...</span>
            </button>
            <button type="button" @click="document.getElementById('email').value='financeiro@clinicafabris.com.br'; document.getElementById('password').value='financeiro123';"
                    class="p-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100/80 text-emerald-900 text-left border border-emerald-200 transition">
                <span class="font-bold block text-[11px] text-emerald-700">Financeiro</span>
                <span class="text-[10px] text-slate-500 block truncate">financeiro@clinica...</span>
            </button>
        </div>
    </div>
@endsection
