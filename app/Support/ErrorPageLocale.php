<?php

namespace App\Support;

use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use RuntimeException;

class ErrorPageLocale
{
    public static function resolve(Request $request): string
    {
        $supported = ['ar', 'en'];
        $preference = $request->query('lang');
        if (in_array($preference, $supported, true)) {
            return $preference;
        }

        $preference = $request->hasSession() ? $request->session()->get('locale') : null;
        if (in_array($preference, $supported, true)) {
            return $preference;
        }

        $cookie = $request->cookie('gang_locale');
        if (in_array($cookie, $supported, true)) {
            return $cookie;
        }

        // Routing errors can be rendered before the web middleware decrypts cookies.
        if (is_string($cookie) && config('app.key')) {
            try {
                $encrypter = app('encrypter');
                $value = $encrypter->decrypt($cookie, false);
                $preference = is_string($value)
                    ? CookieValuePrefix::validate('gang_locale', $value, $encrypter->getAllKeys())
                    : null;
                if (in_array($preference, $supported, true)) {
                    return $preference;
                }
            } catch (RuntimeException) {
                // A stale cookie or unavailable encryption key should not break the error page.
            }
        }

        return app()->isLocale('ar') ? 'ar' : 'en';
    }
}
