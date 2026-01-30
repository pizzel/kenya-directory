<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetIntendedUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('redirect') && $request->method() === 'GET') {
            session(['url.intended' => $request->input('redirect')]);
        }
        return $next($request);
    }
}