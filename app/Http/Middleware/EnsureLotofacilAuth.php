<?php
// app/Http/Middleware/EnsureLotofacilAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLotofacilAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('lotofacil_authenticated')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
