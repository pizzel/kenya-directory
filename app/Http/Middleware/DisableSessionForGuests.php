<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlePublicCaching
{
    /**
     * A list of EXACT route names that are eligible for public caching for guests.
     * This is the safest and most precise way to control caching.
     * @var array
     */
    protected array $cacheableRoutes = [
        'home',
        'listings.search',
        'listings.county',
        'listings.category',
        'listings.facility',
        'listings.tag',
        'listings.index',
        'collections.index',
        'events.index.public',
        'events.by_county',
        'posts.index',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the current request matches our conditions for public caching.
        $isCacheable = in_array($request->route()?->getName(), $this->cacheableRoutes)
                       && $request->isMethod('get')
                       && auth()->guest();

        // If it's a cacheable request, disable the session driver for this request only.
        // This is the key to preventing the Set-Cookie header.
        if ($isCacheable) {
            config(['session.driver' => 'array']);
        }

        // Let the request continue and get the response from the application.
        $response = $next($request);

        // If it was a cacheable request, add the public caching headers to the final response.
        if ($isCacheable) {
            $response->headers->set('Cache-Control', 'public, max-age=10800, s-maxage=10800');
            $response->headers->remove('Pragma');
        }

        return $response;
    }
}