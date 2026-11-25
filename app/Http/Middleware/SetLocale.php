<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestedLocale = $request->query('lang');

        $switchingLocale = in_array($requestedLocale, ['ar', 'en'], true);

        if ($switchingLocale) {
            $request->session()->put('locale', $requestedLocale);
        }

        $locale = $request->session()->get('locale') ?? $request->cookie('gang_locale');
        app()->setLocale(in_array($locale, ['ar', 'en'], true) ? $locale : config('app.locale', 'ar'));

        $response = $next($request);

        if ($switchingLocale) {
            $response->headers->setCookie(cookie('gang_locale', $requestedLocale, 60 * 24 * 365));
        }

        return $response;
    }
}
