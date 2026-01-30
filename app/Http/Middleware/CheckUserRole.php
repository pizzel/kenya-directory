<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // Allow multiple roles later if needed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->role !== $role) {
            // If you want to allow admins to also access business owner routes:
            // if (!$request->user() || ($request->user()->role !== $role && !$request->user()->isAdmin())) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}