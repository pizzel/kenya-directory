<?php

namespace App\Support\CacheProfiles;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class CacheLoggedInUsers extends CacheAllSuccessfulGetRequests
{
    /**
     * Determine if the request may be cached.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function shouldCacheRequest(Request $request): bool
    {
        // This is the key. The original method checks for a session cookie here.
        // We are overriding it to ONLY check if it's a GET request.
        // This will allow pages with sessions to be cached.
        if ($request->ajax()) {
            return false;
        }

        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }
}