<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale based on the route parameter.
     * Falls back to default locale if the provided locale is not supported.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $supported = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        // Validate and set locale
        if ($locale && in_array($locale, $supported, true)) {
            app()->setLocale($locale);
        } else {
            app()->setLocale($default);
        }

        return $next($request);
    }
}
