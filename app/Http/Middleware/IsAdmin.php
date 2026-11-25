<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'Acesso negado.');
        }

        if(!$request->user()->is_admin){
           return redirect()->route('dashboard')->withErrors(['geral' => 'Usuário sem permissão.']);
        }

        return $next($request);
    }
}
