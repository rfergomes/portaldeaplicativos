<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UppercaseAtivoStrings
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
            // Campos que não devem ser convertidos para maiúsculas
            // _token, _method: Laravel internals
            // password, email: Sensitive/Standard formats
            // status, tipo: Enums/Slugs
            // *_id: Foreign keys
            // data_*, *_em: Date fields
            $ignoredKeys = [
                '_token', 
                '_method', 
                'password', 
                'email', 
                'status', 
                'tipo', 
                'tipo_uso'
            ];

            $isId = str_ends_with($key, '_id') || $key === 'id';
            $isDate = str_starts_with($key, 'data_') || str_ends_with($key, '_em') || str_ends_with($key, '_at');

            if (is_string($value) && !in_array($key, $ignoredKeys) && !$isId && !$isDate) {
                $value = mb_strtoupper($value, 'UTF-8');
            }
        });

        $request->replace($input);

        return $next($request);
    }
}
