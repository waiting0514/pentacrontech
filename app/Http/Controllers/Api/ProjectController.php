<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Services\ProjectMailService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        return $this->successResponse($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('system.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'yearly_budget_twd' => 'required|numeric|min:0',
        ]);

        $project = Project::create($validated);

        return $this->successResponse($project, 'Project created', 201);
    }

    public function show(Project $project): JsonResponse
    {
        return $this->successResponse($project);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('system.manage');

        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.zh-TW' => 'nullable|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'yearly_budget_twd' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'is_ai_enabled' => 'sometimes|boolean',
            'product_default_inquiry_mode' => 'sometimes|boolean',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|array',
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'nullable|array',
            // SMTP
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|in:25,465,587,2525',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            // CAPTCHA
            'captcha_provider' => 'nullable|string|in:none,recaptcha,turnstile',
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            'turnstile_site_key' => 'nullable|string|max:255',
            'turnstile_secret_key' => 'nullable|string|max:255',
        ]);

        // Don't overwrite secrets if not provided
        foreach (['smtp_password', 'recaptcha_secret_key', 'turnstile_secret_key'] as $secret) {
            if (!$request->filled($secret)) {
                unset($validated[$secret]);
            }
        }

        $project->update($validated);

        return $this->successResponse($project, 'Project updated');
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('system.manage');

        $project->delete();

        return $this->successResponse(null, 'Project deleted');
    }

    public function current(): JsonResponse
    {
        $project = Project::firstOrFail();

        // 回傳完整翻譯資料
        $data = $project->toArray();
        $data['name'] = $project->getTranslations('name');
        $data['meta_title'] = $project->getTranslations('meta_title');
        $data['meta_description'] = $project->getTranslations('meta_description');
        $data['meta_keywords'] = $project->getTranslations('meta_keywords');
        $data['contact_address'] = $project->getTranslations('contact_address');
        $data['maintenance_message'] = $project->getTranslations('maintenance_message');
        $data['has_smtp_password'] = !empty($project->smtp_password);
        $data['has_recaptcha_secret_key'] = !empty($project->recaptcha_secret_key);
        $data['has_turnstile_secret_key'] = !empty($project->turnstile_secret_key);

        return $this->successResponse($data);
    }

    public function switch(Request $request, Project $project): JsonResponse
    {
        $this->authorize('system.manage');

        return $this->successResponse($project, 'Switched to project: ' . $project->name);
    }

    public function testSmtp(Project $project, ProjectMailService $mailService): JsonResponse
    {
        $this->authorize('system.manage');

        if (!$mailService->hasSmtpConfig($project)) {
            return $this->errorResponse('請先設定 SMTP 主機與帳號', 'SMTP_NOT_CONFIGURED', 422);
        }

        if (!$project->contact_email) {
            return $this->errorResponse('請先設定聯絡信箱（contact_email）作為測試收件人', 'NO_CONTACT_EMAIL', 422);
        }

        try {
            $fromAddress = $project->smtp_from_address ?? $project->smtp_username;
            $fromName = $project->smtp_from_name ?? $project->name;

            $mailService->getMailer($project)->raw(
                '這是一封測試信，確認 SMTP 設定正確。',
                function ($message) use ($project, $fromAddress, $fromName) {
                    $message->to($project->contact_email)
                            ->from($fromAddress, $fromName)
                            ->subject('SMTP 測試信');
                }
            );

            return $this->successResponse(null, '測試信已發送至 ' . $project->contact_email);
        } catch (\Throwable $e) {
            return $this->errorResponse('寄信失敗：' . $e->getMessage(), 'SMTP_SEND_FAILED', 422);
        }
    }

    public function statistics(Project $project): JsonResponse
    {
        return $this->successResponse([
            'name' => $project->name,
            'yearly_budget_twd' => $project->yearly_budget_twd,
            'yearly_usage_twd' => $project->yearly_usage_twd,
            'remaining_budget' => $project->remaining_budget,
            'usage_percentage' => $project->usage_percentage,
            'is_budget_exceeded' => $project->isBudgetExceeded(),
            'products_count' => $project->products()->count(),
            'usage_logs_count' => $project->aiUsageLogs()->count(),
        ]);
    }
}
