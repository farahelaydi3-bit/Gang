<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->phone_verified_at) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phone verification required.',
                ], 403);
            }

            return redirect()->route('phone.verify');
        }

        return $next($request);
    }
}