<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') - {{ config('app.name', 'Clínica Fabris') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full antialiased font-sans text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar (Desktop & Mobile Drawer) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:flex-shrink-0">
            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between px-6 bg-slate-950/60 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-500 flex items-center justify-center text-white font-bold shadow-md shadow-teal-500/20">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-base font-bold text-white tracking-wide block leading-tight">Clínica Fabris</span>
                        <span class="text-[10px] text-teal-400 uppercase tracking-wider font-semibold">Fisioterapia</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1 text-sm font-medium">
                <!-- Painel -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('dashboard*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Painel Inicial</span>
                </a>

                <!-- Pacientes -->
                @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('pacientes.view'))
                <a href="{{ route('pacientes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('pacientes*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Pacientes</span>
                </a>
                @endif

                <!-- Agenda -->
                @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('agenda.view'))
                <a href="{{ route('agenda.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('agenda*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span>Agenda</span>
                </a>
                @endif

                <!-- Atendimentos & Prontuário -->
                @if(auth()->user()->isProprietaria() || auth()->user()->isFisioterapeuta() || auth()->user()->hasPermissionTo('prontuario.view'))
                <a href="{{ route('prontuarios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('prontuarios*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="stethoscope" class="w-5 h-5"></i>
                    <span>Prontuários & Evolução</span>
                </a>
                @endif

                <!-- Catálogo de Serviços, Produtos e Pacotes -->
                @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('servicos.view') || auth()->user()->hasPermissionTo('configuracoes.manage'))
                <div class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Catálogo & Itens</p>
                </div>
                <a href="{{ route('servicos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('servicos*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>Serviços & Procedimentos</span>
                </a>
                <a href="{{ route('produtos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('produtos*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span>Produtos & Estoque</span>
                </a>
                <a href="{{ route('pacotes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('pacotes*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="tags" class="w-5 h-5"></i>
                    <span>Pacotes de Sessões</span>
                </a>
                @endif

                <!-- Vendas & Pacotes -->
                @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->isFinanceiro() || auth()->user()->hasPermissionTo('vendas.view'))
                <div class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Comercial</p>
                </div>
                <a href="{{ route('vendas.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('vendas*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    <span>Vendas</span>
                </a>
                <a href="{{ route('pacientes-pacotes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('pacientes-pacotes*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                    <span>Saldos de Pacotes</span>
                </a>
                @endif

                <!-- Financeiro -->
                @if(auth()->user()->isProprietaria() || auth()->user()->isFinanceiro() || auth()->user()->hasPermissionTo('financeiro.view'))
                <div class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Financeiro</p>
                </div>
                <a href="{{ route('financeiro.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('financeiro.index') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                    <span>Fluxo & Caixa</span>
                </a>
                <a href="{{ route('financeiro.receber') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('financeiro.receber*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="arrow-down-left" class="w-5 h-5 text-emerald-400"></i>
                    <span>Contas a Receber</span>
                </a>
                <a href="{{ route('financeiro.pagar') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('financeiro.pagar*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="arrow-up-right" class="w-5 h-5 text-rose-400"></i>
                    <span>Contas a Pagar</span>
                </a>
                <a href="{{ route('financeiro.repasses') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('financeiro.repasses*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="user-check" class="w-5 h-5 text-purple-400"></i>
                    <span>Repasses / Comissões</span>
                </a>
                @endif

                <!-- Relatórios -->
                @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('relatorios.view'))
                <div class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Gestão & Inteligência</p>
                </div>
                <a href="{{ route('relatorios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('relatorios*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span>Relatórios Dinâmicos</span>
                </a>
                @endif

                <!-- Convênios -->
                @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('convenios.manage'))
                <a href="{{ route('convenios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('convenios*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                    <span>Convênios & Guias</span>
                </a>
                @endif

                <!-- Configurações (Apenas Proprietária / Autorizado) -->
                @if(auth()->user()->isProprietaria() || auth()->user()->hasPermissionTo('configuracoes.manage'))
                <div class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Administração</p>
                </div>
                <a href="{{ route('configuracoes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('configuracoes*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Configurações</span>
                </a>
                <a href="{{ route('usuarios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('usuarios*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span>Usuários & Acessos</span>
                </a>
                <a href="{{ route('auditoria.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('auditoria*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                    <span>Trilha de Auditoria</span>
                </a>
                @endif
            </nav>

            <!-- User Footer Info -->
            <div class="p-4 bg-slate-950/80 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-block px-2 py-0.5 text-[10px] rounded-full font-medium bg-teal-500/20 text-teal-300">
                            {{ auth()->user()->role_label }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sair do sistema" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">@yield('header', 'Painel')</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Data Atual -->
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                        <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                        <span>{{ now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                    </div>

                    <!-- Atalho Novo Paciente / Novo Agendamento -->
                    @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('agenda.create'))
                    <a href="{{ route('agenda.index', ['novo' => 1]) }}"
                       class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-3 py-2 rounded-xl shadow-sm transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Novo Agendamento</span>
                    </a>
                    @endif
                </div>
            </header>

            <!-- Flash Alerts -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center gap-3 shadow-sm" x-data="{ show: true }" x-show="show">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                        <div class="flex-1 font-medium">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-center gap-3 shadow-sm" x-data="{ show: true }" x-show="show">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                        <div class="flex-1 font-medium">{{ session('error') }}</div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 shadow-sm">
                        <div class="font-semibold flex items-center gap-2 mb-1">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                            Revise os dados preenchidos:
                        </div>
                        <ul class="list-disc pl-5 space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 pb-12">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
