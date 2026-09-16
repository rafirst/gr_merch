<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware pembatas akses berdasarkan role user.
 * Contoh pemakaian di routes: ->middleware('role:admin_ho')
 */
class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
