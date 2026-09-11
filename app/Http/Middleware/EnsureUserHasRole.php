<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route (or route group) to users whose `role` column matches
 * one of the roles passed in as middleware parameters.
 *
 * Usage: ->middleware('role:admin,incharge')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'You are not authorized to access this section.');
        }

        return $next($request);
    }
}
