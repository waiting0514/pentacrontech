<?php

namespace App\Http\Controllers\Api;

use App\Models\AiUsageLog;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UsageController extends Controller
{
    use ApiResponse;

    public function current(Request $request): JsonResponse
    {
        $this->authorize('system.manage');

        $setting = SiteSetting::current();

        $data = [
            'usage_percentage' => $setting->yearly_budget_twd > 0
                ? round(($setting->yearly_usage_twd / $setting->yearly_budget_twd) * 100, 2)
                : 0,
            'site_name' => $setting->name,
            'yearly_usage_twd' => (float) $setting->yearly_usage_twd,
            'yearly_budget_twd' => (float) $setting->yearly_budget_twd,
            'remaining_budget_twd' => max(0, $setting->yearly_budget_twd - $setting->yearly_usage_twd),
        ];

        return $this->successResponse($data);
    }

    public function logs(Request $request): JsonResponse
    {
        $this->authorize('system.manage');


        $query = AiUsageLog::query()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc');

        // Only select cost_usd and token fields for super-admin
        if (!$request->user()->hasRole('super-admin')) {
            $query->select([
                'id', 'user_id', 'provider', 'feature',
                'display_cost_twd', 'status', 'created_at', 'updated_at',
            ]);
        }

        $logs = $query->paginate(20);

        return $this->successResponse($logs);
    }

    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('system.manage');

        $setting = SiteSetting::current();

        $selectRaw = 'provider, feature, COUNT(*) as count, COALESCE(SUM(display_cost_twd), 0) as total_cost_twd';
        if ($request->user()->hasRole('super-admin')) {
            $selectRaw .= ', COALESCE(SUM(cost_usd), 0) as total_cost_usd';
        }

        $stats = AiUsageLog::query()
            ->selectRaw($selectRaw)
            ->groupBy('provider', 'feature')
            ->get();

        return $this->successResponse([
            'statistics' => $stats,
            'total_usage_twd' => (float) $setting->yearly_usage_twd,
            'site_name' => $setting->name,
        ]);
    }
}
