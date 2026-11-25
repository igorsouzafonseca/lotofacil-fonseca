<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup — Criar Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Criar administrador</h1>
            <p class="text-sm text-slate-500 mt-1">
                Esta página funciona apenas uma vez. Após criar o admin, ela se desativa automaticamente.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.admin') }}?token={{ request('token') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ request('token') }}">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nome</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Senha (mín. 10)</label>
                <input type="password" name="password" required minlength="10"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar senha</label>
                <input type="password" name="password_confirmation" required minlength="10"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 text-sm transition">
                Criar administrador
            </button>
        </form>
    </div>

</body>
</html>
