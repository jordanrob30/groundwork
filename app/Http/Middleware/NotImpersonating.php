<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotImpersonating
{
    /**
     * Handle an incoming request.
     *
     * Blocks sensitive actions when an admin is impersonating another user.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonating')) {
            abort(403, 'This action cannot be performed while impersonating a user');
        }

        return $next($request);
    }
}
