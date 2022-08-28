<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
//use Illuminate\Http\Response;


class CORS
{
    public function handle(Request $request, Closure $next)
    {
        //return $next($request);
        /*return $next($request)
            ->header('Access-Control-Allow-Origin', config('cors.allowed_origins'))
            ->header('Access-Control-Allow-Methods', config('cors.allowed_methods'))
            ->header('Access-Control-Allow-Headers',config('cors.allowed_headers'));
        */
        /*
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', '*');
        */
/*
        header("Access-Control-Allow-Origin: *");

        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin'
        ];
        if($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        foreach($headers as $key => $value)
            $response->header($key, $value);
*/
        $response = $next($request);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', 'http://10.0.2.2:8000');
        $response->headers->set('Access-Control-Allow-Credentials', true);
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
        return $response;
    }
}

/*
class Cors
{
    private static $allowedOriginsWhitelist = [
        'http://localhost:8000'
    ];

    // All the headers must be a string

    private static $allowedOrigin = '*';

    private static $allowedMethods = 'OPTIONS, GET, POST, PUT, PATCH, DELETE';

    private static $allowCredentials = 'true';

    private static $allowedHeaders = '';

    public function handle($request, Closure $next)
    {
        if (! $this->isCorsRequest($request))
        {
            return $next($request);
        }

        static::$allowedOrigin = $this->resolveAllowedOrigin($request);

        static::$allowedHeaders = $this->resolveAllowedHeaders($request);

        $headers = [
            'Access-Control-Allow-Origin'       => static::$allowedOrigin,
            'Access-Control-Allow-Methods'      => static::$allowedMethods,
            'Access-Control-Allow-Headers'      => static::$allowedHeaders,
            'Access-Control-Allow-Credentials'  => static::$allowCredentials,
        ];

        // For preflighted requests
        if ($request->getMethod() === 'OPTIONS')
        {
            return response('', 200)->withHeaders($headers);
        }

        $response = $next($request)->withHeaders($headers);

        return $response;
    }

    private function isCorsRequest($request)
    {
        $requestHasOrigin = $request->headers->has('Origin');

        if ($requestHasOrigin)
        {
            $origin = $request->headers->get('Origin');

            $host = $request->getSchemeAndHttpHost();

            if ($origin !== $host)
            {
                return true;
            }
        }

        return false;
    }

    private function resolveAllowedOrigin($request)
    {
        $allowedOrigin = static::$allowedOrigin;

        // If origin is in our $allowedOriginsWhitelist
        // then we send that in Access-Control-Allow-Origin

        $origin = $request->headers->get('Origin');

        if (in_array($origin, static::$allowedOriginsWhitelist))
        {
            $allowedOrigin = $origin;
        }

        return $allowedOrigin;
    }
//https://stackoverflow.com/questions/33076705/laravel-5-1-api-enable-cors
    private function resolveAllowedHeaders($request)
    {
        $allowedHeaders = $request->headers->get('Access-Control-Request-Headers');

        return $allowedHeaders;
    }
}
*/
