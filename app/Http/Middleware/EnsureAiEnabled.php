<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = SiteSetting::current();

        if (!$site || !$site->is_ai_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are disabled for this site.',
                'error_code' => 'AI_DISABLED'
            ], 403);
        }

        return $next($request);
    }
}