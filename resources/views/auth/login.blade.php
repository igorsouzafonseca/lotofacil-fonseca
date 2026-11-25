<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lotofácil Analyzer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">Lotofácil</h1>
            <p class="text-gray-500 text-sm mt-1">Analisador Estatístico</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Acessar o sistema</h2>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 text-sm px-4 py-3 rounded-md mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="email"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 border">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha
                    </label>
                    <input type="password" name="password" id="password"
                           required autocomplete="current-password"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 border">
                </div>

                <button type="submit"
                        class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 transition shadow">
                    Entrar
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Acesso restrito. Credenciais definidas no arquivo <code>.env</code>.
        </p>
    </div>
</body>
</html>
