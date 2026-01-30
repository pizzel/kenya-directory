<?php

namespace App\Support\Serializers;

use Spatie\ResponseCache\Serializers\DefaultSerializer;
use Symfony\Component\HttpFoundation\Response;

class StripXsrfTokenSerializer extends DefaultSerializer
{
    /**
     * We override the default serialize method.
     * The method signature now perfectly matches the parent class.
     */
    public function serialize(Response $response): string
    {
        // This is the key: we remove the XSRF-TOKEN cookie from the response
        // *before* it gets serialized and saved to the cache. We also specify
        // the path and domain to be 100% sure we are removing the right cookie.
        $response->headers->removeCookie(
            'XSRF-TOKEN', 
            config('session.path'), 
            config('session.domain')
        );
        
        // After removing the cookie, we call the parent's original serialize method.
        return parent::serialize($response);
    }
}