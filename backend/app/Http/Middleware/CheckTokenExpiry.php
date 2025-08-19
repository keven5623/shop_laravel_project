<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->noContent(); // 直接回 200，不檢查 token
        }
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken && $accessToken->expires_at && now()->greaterThan($accessToken->expires_at)) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}

