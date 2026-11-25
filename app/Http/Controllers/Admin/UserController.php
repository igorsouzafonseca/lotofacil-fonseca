<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::latest()->get();
        return view('admin.usuarios', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'is_admin' => ['required', 'in:0,1'],
        ]);

        User::create([
        'name'     => $dados['name'],
        'email'    => $dados['email'],
        'password' => Hash::make($dados['password']),
        'is_admin' => (bool) $dados['is_admin'],
    ]);

    return redirect()
        ->route('admin.usuarios')
        ->with('sucesso', 'Usuário criado com sucesso!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id) {
            return back()->withErrors(['geral' => 'Você não pode excluir a si mesmo.']);
        }
        $user->delete();
        return back()->with('sucesso', 'Usuário removido.');
    }
}
