<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class HandlePublicCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('get') && auth()->guest() &&
            (
                $request->is('/') ||
                $request->is('listings') ||
                $request->is('listings/*') ||
                $request->is('collections') ||
                $request->is('events') ||
                $request->is('events/*') ||
                $request->is('blog')
            )
        ) {
            // Public cacheable route
            $response->headers->set('Cache-Control', 'public, max-age=10800');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 10800));
            $response->headers->set('Pragma', 'public');
            $response->headers->remove('Set-Cookie');

            Log::info('[AddPublicCacheHeaders] Public cacheable route matched: ' . $request->url());
        }

        return $response;
    }
}
