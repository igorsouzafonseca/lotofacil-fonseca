<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') — {{ setting('site.name', 'Sistema') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eef2ff', 100: '#e0e7ff', 500: '#6366f1',
                            600: '#4f46e5', 700: '#4338ca', 900: '#312e81',
                        },
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">

<div class="flex min-h-screen">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col">

        {{-- Logo --}}
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg bg-brand-600 grid place-items-center text-white font-bold">A</div>
            <span class="text-white font-semibold text-lg tracking-tight">
                {{ setting('site.name', 'AdminPanel') }}
            </span>
        </div>

        {{-- Navegação --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Painel</p>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 hover:text-white transition
            {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <a href="{{ route('generator.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 hover:text-white transition
            {{ request()->routeIs('generator.index') || request()->routeIs('generator.generate') ? 'bg-brand-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="9" cy="9" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="9" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="9" cy="15" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="15" r="1.5" fill="currentColor" stroke="none"/>
                </svg>
                Gerador de Jogos
            </a>
            @can('admin')
                <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Administração</p>

                <a href="{{ route('admin.usuarios') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('admin.usuarios*') ? 'bg-brand-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a3 3 0 10-2-5.24"/></svg>
                    Usuários
                </a>

                <a href="{{ route('admin.importacoes') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('admin.importacoes*') ? 'bg-brand-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.9 1.1 4 4 0 01-.9 7.8M12 12v9m0-9l-3 3m3-3l3 3"/>
                    </svg>
                    Importações
                </a>

                <a href="{{ route('admin.configuracoes') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('admin.configuracoes*') ? 'bg-brand-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configurações
                </a>
            @endcan
        </nav>

        {{-- Usuário logado --}}
        <div class="border-t border-slate-800 p-3">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                <div class="w-9 h-9 rounded-full bg-brand-600 text-white grid place-items-center font-semibold text-sm shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Usuário' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

    {{-- ===================== CONTEÚDO ===================== --}}
    <div class="flex-1 flex flex-col lg:ml-64 min-w-0">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-3">
                <button id="menuBtn" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">@yield('title', 'Painel')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online
                </span>
            </div>
        </header>

        {{-- MAIN --}}
        <main class="flex-1 p-4 lg:p-8">
            {{-- Alertas flash --}}
            @if(session('sucesso'))
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('sucesso') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggle = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);
    };
    document.getElementById('menuBtn')?.addEventListener('click', () => toggle(true));
    overlay?.addEventListener('click', () => toggle(false));
</script>
<script src="https://code.jquery.com/jquery-4.1.0.min.js"></script>
@stack('scripts')
</body>
</html>
