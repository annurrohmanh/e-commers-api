<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class JwtBlacklist
{
    public function __construct(
        private readonly JWTAuth $jwt,
        private readonly RedisFactory $redis,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $next($request);
        }

        try {
            $this->jwt->setToken($token);
            $jti = $this->jwt->getPayload()->get('jti');

            if ($this->redis->connection()->exists('blacklist:'.$jti)) {
                return ApiResponse::error('token_revoked', null, 401);
            }
        } catch (\Throwable) {
            return $next($request);
        }

        return $next($request);
    }
}
