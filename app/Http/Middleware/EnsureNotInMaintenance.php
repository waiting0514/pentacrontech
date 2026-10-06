<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInMaintenance
{
    /**
     * 專案開啟維護模式時，前台所有頁面都顯示維護畫面（503）。
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = SiteSetting::current();

        if ($site?->maintenance_mode) {
            return response()->view('pages.maintenance', [
                'site' => $site,
                'message' => $site->maintenance_message,
            ], 503);
        }

        return $next($request);
    }
}
