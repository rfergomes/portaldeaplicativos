<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->force_password_change) {
            // Se estiver na rota de troca de senha ou tentando deslogar, permite
            if (!$request->is('password/change*') && !$request->is('logout')) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
