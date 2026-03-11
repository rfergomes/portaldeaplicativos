<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UppercaseAgendaStrings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            // Campos que não devem ser convertidos para maiúsculas (senhas, tokens, emails, status internos)
            $ignoredKeys = ['_token', '_method', 'password', 'email', 'email_confirmation', 'status', 'email_hospede'];
            if (is_string($value) && !in_array($key, $ignoredKeys)) {
                $value = mb_strtoupper($value, 'UTF-8');
            }
        });

        $request->replace($input);

        return $next($request);
    }
}
