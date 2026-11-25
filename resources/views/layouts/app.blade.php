<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel') — {{ setting('site.name', 'Sistema') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @yield('css')
</head>
<body class="bg-gray-100 font-sans antialiased">
    <nav class="bg-white shadow mb-6">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center">
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.usuarios') }}" class="px-5 text-gray-700 hover:text-green-600 font-medium">Administrador</a>
            @endif
            <a href="{{ route('dashboard') }}" class="px-5 text-gray-700 hover:text-green-600 font-medium">Dashboard</a>
            <a href="{{ route('generator.index') }}" class="text-gray-700 hover:text-green-600 font-medium">Gerador de Jogos</a>

            <div class="ml-auto flex items-center gap-4">
                <span class="text-sm text-gray-500">
                    Olá, <strong class="text-gray-700">{{ auth()->user()->name }}</strong>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-sm text-red-600 hover:text-red-800 font-medium transition">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main>
        @if($errors->any())
                <div class="w-[93%] mx-auto mt-0 mb-0 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                    <label>{{ $errors->first()}}</label>
                </div>
            @endif

        {{ $slot }}
    </main>
    <script src="https://code.jquery.com/jquery-4.1.0.min.js"></script>
    @yield('scripts')
</body>
</html>
