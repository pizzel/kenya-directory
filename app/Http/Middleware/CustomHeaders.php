<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Let the application generate the response first.
        $response = $next($request);

        // Now, add our public caching headers.
        $response->headers->set('Cache-Control', 'public, max-age=10800');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 10800));
        $response->headers->set('Pragma', 'public');

        return $response;
    }
}