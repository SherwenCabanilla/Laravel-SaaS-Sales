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
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        // Handle comma-separated string if passed as a single argument (e.g. from route string)
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = explode(',', $roles[0]);
        }

        // Check if user has ANY of the allowed roles
        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
