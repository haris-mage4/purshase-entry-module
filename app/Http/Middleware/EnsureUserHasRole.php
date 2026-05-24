<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  \Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)
            ->map(fn (string $role) => Role::tryFrom($role))
            ->filter()
            ->all();

        if ($allowed === [] || ! in_array($user->role, $allowed, true)) {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
