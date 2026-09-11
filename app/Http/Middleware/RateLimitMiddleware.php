<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    // Tambahkan parameter $customLimit di akhir method handle
    public function handle(Request $request, Closure $next, ?int $customLimit = null): Response
    {
        // 1. Jika ada parameter dari route, pakai itu. Jika tidak, ambil dari .env/config (default 60)
        $limit = $customLimit ?? (int) config('app.rate_limit_per_minute', env('RATE_LIMIT_PER_MINUTE', 60));

        $identifier = auth()->guard('api')->id() ?? $request->ip();
        $key = 'rate_limit:'.$identifier;

        // 2. Cek apakah user sudah melewati batas
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests.',
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => (string) $limit,
                'X-RateLimit-Remaining' => '0',
                'Retry-After' => (string) $retryAfter,
            ]);
        }

        // 3. Catat request
        RateLimiter::hit($key, 60);

        $response = $next($request);

        // 4. Hitung sisa kuota
        $remaining = RateLimiter::remaining($key, $limit);

        return $response->withHeaders([
            'X-RateLimit-Limit' => (string) $limit,
            'X-RateLimit-Remaining' => (string) $remaining,
        ]);
    }
}