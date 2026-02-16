<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roleSlug
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roleSlug)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        // Using hasRole() from User model
        if (!$user->hasRole($roleSlug)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
