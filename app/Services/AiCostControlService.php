<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Log;

class AiCostControlService
{
    /**
     * Get the fixed TWD price for an AI feature.
     *
     * @param string $feature
     * @return float Price in TWD
     */
    public function getFeaturePrice(string $feature): float
    {
        return (float) config("ai_pricing.{$feature}", config('ai_pricing.default', 10));
    }

    /**
     * Check if project has exceeded yearly budget.
     *
     * @param Project $project
     * @param float $estimatedCostTwd Estimated cost in TWD (fixed price)
     * @return bool True if within budget, false if exceeded
     */
    public function checkBudget(Project $project, float $estimatedCostTwd = 0): bool
    {
        $usage = (float) $project->yearly_usage_twd;
        $budget = (float) $project->yearly_budget_twd;
        $remaining = $budget - $usage;

        if ($remaining < $estimatedCostTwd) {
            Log::warning('Project budget insufficient', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'usage_twd' => $usage,
                'budget_twd' => $budget,
                'remaining_twd' => $remaining,
                'estimated_cost_twd' => $estimatedCostTwd,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Record AI usage to the database.
     *
     * @param User $user
     * @param Project $project
     * @param array $logData
     * @return AiUsageLog
     */
    public function recordUsage(User $user, Project $project, array $logData): AiUsageLog
    {
        $status = $logData['status'] ?? (($logData['is_successful'] ?? true) ? 'success' : 'failed');

        return AiUsageLog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'provider' => $logData['provider'] ?? $logData['service'] ?? 'openai',
            'feature' => $logData['feature'] ?? null,
            'input_tokens' => $logData['input_tokens'] ?? null,
            'output_tokens' => $logData['output_tokens'] ?? null,
            'cost_usd' => $logData['cost_usd'] ?? 0,
            'display_cost_twd' => $logData['display_cost_twd'] ?? 0,
            'status' => $status,
        ]);
    }

    /**
     * Increment project's yearly usage in TWD.
     *
     * @param Project $project
     * @param float $costTwd Fixed price in TWD
     * @return void
     */
    public function incrementUsage(Project $project, float $costTwd): void
    {
        $project->increment('yearly_usage_twd', $costTwd);
    }

    /**
     * Calculate cost for OpenAI API call using config prices.
     *
     * @param string $model
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float Cost in USD
     */
    public function calculateOpenAICost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = config('ai_prices.openai');
        $rates = $pricing[$model] ?? $pricing['gpt-4o-mini'];

        return ($inputTokens * $rates['input']) + ($outputTokens * $rates['output']);
    }

    /**
     * Estimate cost for third-party image API using config prices.
     *
     * @param string $provider
     * @return float Estimated cost in USD
     */
    public function estimateImageCost(string $provider): float
    {
        $costs = config('ai_prices');

        return match($provider) {
            'photoroom' => $costs['photoroom']['scene_generation'] ?? 0.05,
            'clipdrop' => $costs['clipdrop']['remove_background'] ?? 0.03,
            default => 0.05
        };
    }

    /**
     * Get current project (API 使用 token 驗證，沒有 session，一律取第一個專案)。
     *
     * @return Project
     */
    public function getCurrentProject(): Project
    {
        return Project::firstOrFail();
    }
}
