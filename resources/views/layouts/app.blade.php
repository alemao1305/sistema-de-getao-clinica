<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') - {{ config('app.name', 'Clínica Fabris') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
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

                <!-- Salas & Boxes de Atendimento -->
                @if(auth()->user()->isProprietaria() || auth()->user()->isRecepcao() || auth()->user()->hasPermissionTo('configuracoes.manage'))
                <a href="{{ route('salas.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('salas*') ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i data-lucide="door-open" class="w-5 h-5"></i>
                    <span>Salas & Boxes</span>
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

                    <!-- Botão Alternador de Tema Dark / Light -->
                    <button type="button"
                            id="theme-toggle"
                            onclick="toggleTheme()"
                            class="p-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 transition flex items-center justify-center"
                            title="Alternar Modo Escuro / Claro">
                        <i id="theme-toggle-dark-icon" data-lucide="moon" class="w-4 h-4 text-indigo-400"></i>
                        <i id="theme-toggle-light-icon" data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                    </button>

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

    <!-- CEP Mask & Auto-fill Handler Global -->
    <script>
    (function() {
        function formatCep(val) {
            if (!val) return '';
            const digits = String(val).replace(/\D/g, '').slice(0, 8);
            if (digits.length > 5) {
                return digits.slice(0, 5) + '-' + digits.slice(5);
            }
            return digits;
        }

        function isCepField(el) {
            if (!el || el.tagName !== 'INPUT') return false;
            const id = (el.id || '').toLowerCase();
            const name = (el.name || '').toLowerCase();
            const ph = (el.placeholder || '').toLowerCase();
            return id === 'cep' || name === 'cep' || el.dataset.mask === 'cep' || ph.includes('00000');
        }

        async function fetchAddress(cleanCep) {
            // 1. ViaCEP
            try {
                const res = await fetch('https://viacep.com.br/ws/' + cleanCep + '/json/');
                if (res.ok) {
                    const data = await res.json();
                    if (!data.erro) {
                        return {
                            logradouro: data.logradouro || '',
                            bairro: data.bairro || '',
                            cidade: data.localidade || '',
                            estado: data.uf || ''
                        };
                    }
                }
            } catch (e) {}

            // 2. BrasilAPI
            try {
                const res = await fetch('https://brasilapi.com.br/api/cep/v1/' + cleanCep);
                if (res.ok) {
                    const data = await res.json();
                    return {
                        logradouro: data.street || '',
                        bairro: data.neighborhood || '',
                        cidade: data.city || '',
                        estado: data.state || ''
                    };
                }
            } catch (e) {}

            // 3. Backend fallback
            try {
                const res = await fetch('/consulta-cep/' + cleanCep, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (!data.erro) return data;
                }
            } catch (e) {}

            return null;
        }

        function triggerEvent(el) {
            if (!el) return;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function findTargetInputs(cepInput) {
            const form = cepInput.closest('form') || cepInput.closest('.grid') || document;
            return {
                logradouro: form.querySelector('#logradouro, [name="logradouro"], [name="rua"], #rua, [name="endereco"], #endereco'),
                bairro: form.querySelector('#bairro, [name="bairro"]'),
                cidade: form.querySelector('#cidade, [name="cidade"], [name="municipio"], #municipio'),
                estado: form.querySelector('#estado, [name="estado"], [name="uf"], #uf'),
                numero: form.querySelector('#numero, [name="numero"]'),
                feedback: form.querySelector('#cep-feedback'),
                loading: form.querySelector('#cep-loading')
            };
        }

        async function processCep(input) {
            const clean = input.value.replace(/\D/g, '');
            if (clean.length !== 8) return;
            if (input._lastFetched === clean) return;
            input._lastFetched = clean;

            const targets = findTargetInputs(input);
            if (targets.loading) targets.loading.classList.remove('hidden');
            if (targets.feedback) {
                targets.feedback.textContent = 'Buscando endereço...';
                targets.feedback.className = 'text-[11px] text-slate-500 mt-1 block min-h-[16px]';
            }

            const data = await fetchAddress(clean);

            if (targets.loading) targets.loading.classList.add('hidden');

            if (data) {
                if (targets.logradouro && data.logradouro) {
                    targets.logradouro.value = data.logradouro;
                    triggerEvent(targets.logradouro);
                }
                if (targets.bairro && data.bairro) {
                    targets.bairro.value = data.bairro;
                    triggerEvent(targets.bairro);
                }
                if (targets.cidade && data.cidade) {
                    targets.cidade.value = data.cidade;
                    triggerEvent(targets.cidade);
                }
                if (targets.estado && data.estado) {
                    targets.estado.value = data.estado.toUpperCase();
                    triggerEvent(targets.estado);
                }
                if (targets.feedback) {
                    targets.feedback.textContent = 'Endereço preenchido com sucesso!';
                    targets.feedback.className = 'text-[11px] text-emerald-600 font-semibold mt-1 block min-h-[16px]';
                    setTimeout(() => {
                        if (targets.feedback.textContent === 'Endereço preenchido com sucesso!') {
                            targets.feedback.textContent = '';
                        }
                    }, 3000);
                }
                if (targets.numero) {
                    targets.numero.focus();
                }
            } else {
                if (targets.feedback) {
                    targets.feedback.textContent = 'CEP não encontrado. Preencha manualmente.';
                    targets.feedback.className = 'text-[11px] text-amber-600 font-semibold mt-1 block min-h-[16px]';
                }
            }
        }

        function handleInput(e) {
            const el = e.target;
            if (!isCepField(el)) return;

            el.maxLength = 9;
            const clean = el.value.replace(/\D/g, '').slice(0, 8);
            const formatted = formatCep(clean);
            if (el.value !== formatted) {
                el.value = formatted;
            }

            if (clean.length === 8) {
                processCep(el);
            }
        }

        document.addEventListener('input', handleInput, true);
        document.addEventListener('blur', (e) => {
            if (isCepField(e.target)) {
                handleInput(e);
            }
        }, true);

        function formatExisting() {
            document.querySelectorAll('input#cep, input[name="cep"], input[data-mask="cep"]').forEach(input => {
                if (input.value) {
                    input.value = formatCep(input.value);
                    input._lastFetched = input.value.replace(/\D/g, '');
                }
            });
        }

        if (document.readyState !== 'loading') {
            formatExisting();
        } else {
            document.addEventListener('DOMContentLoaded', formatExisting);
        }
    })();
    </script>

    <!-- Theme Toggle Logic -->
    <script>
    (function() {
        function updateThemeIcons() {
            var isDark = document.documentElement.classList.contains('dark');
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');
            if (darkIcon && lightIcon) {
                if (isDark) {
                    darkIcon.classList.add('hidden');
                    lightIcon.classList.remove('hidden');
                } else {
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
                }
            }
        }

        window.toggleTheme = function() {
            var isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        };

        if (document.readyState !== 'loading') {
            updateThemeIcons();
        } else {
            document.addEventListener('DOMContentLoaded', updateThemeIcons);
        }
    })();
    </script>
</body>
</html>
