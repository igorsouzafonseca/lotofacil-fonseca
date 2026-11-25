<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminSetupController extends Controller
{
    /**
     * Exibe o formulário de setup.
     * GET /setup/admin?token=XXX
     */
    public function show(Request $request)
    {
        $this->validarAcesso($request);

        // Se já existe admin, 404 (rota se auto-desabilita)
        if (User::where('is_admin', true)->exists()) {
            abort(404);
        }

        return view('setup.admin');
    }

    /**
     * Cria o primeiro admin.
     * POST /setup/admin?token=XXX
     */
    public function store(Request $request)
    {
        $this->validarAcesso($request);

        if (User::where('is_admin', true)->exists()) {
            abort(404);
        }

        $dados = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:10', 'confirmed'],
        ]);

        $admin = User::create([
            'name'     => $dados['name'],
            'email'    => $dados['email'],
            'password' => Hash::make($dados['password']),
            'is_admin' => true,
        ]);

        // Auditoria
        Log::warning('Admin de setup criado', [
            'user_id' => $admin->id,
            'email'   => $admin->email,
            'ip'      => $request->ip(),
            'ua'      => $request->userAgent(),
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Administrador criado. Faça login para continuar.');
    }

    /**
     * Valida o token secreto. Se falhar, 404 (não revela que a rota existe).
     */
    private function validarAcesso(Request $request): void
    {
        $esperado = config('setup.token');
        $recebido = (string) $request->query('token', $request->input('token', ''));

        if (! $esperado || ! hash_equals($esperado, $recebido)) {
            abort(404);
        }
    }
}
