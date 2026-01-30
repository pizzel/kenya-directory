<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionForGuests
{
    public function handle(Request $request, Closure $next): Response
    {
        // Step 1: Prevent session from starting
        if ($request->isMethod('get') && auth()->guest()) {
            config(['session.driver' => 'array']);
        }

        // Step 2: Continue request and get response
        $response = $next($request);

        // Step 3: Only apply headers to public, cacheable routes
        if (
            $request->is('/') ||
            $request->is('listing') ||
            $request->is('listing/*') ||
            $request->is('category/*') ||
            $request->is('explore/*')
        ) {
            // Add cache headers
            $response->headers->set('Cache-Control', 'public, max-age=10800');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 10800));
            $response->headers->set('Pragma', 'public');

            // Strip Set-Cookie if user is not logged in
            if (auth()->guest()) {
                $response->headers->remove('Set-Cookie');
            }

            // Add custom header for debugging (optional)
            $response->headers->set('X-Public-Cache', 'YES');
        }

        return $response;
    }
}