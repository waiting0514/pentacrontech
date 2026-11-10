<?php

namespace App\Http\Controllers\Api;

use App\Models\SiteSetting;
use App\Services\SiteMailService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class SiteSettingController extends Controller
{
    use ApiResponse;

    private const TRANSLATABLE_FIELDS = [
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'contact_address',
        'maintenance_message',
    ];

    public function show(): JsonResponse
    {
        $setting = SiteSetting::current();

        // 回傳完整翻譯資料
        $data = $setting->toArray();
        foreach (self::TRANSLATABLE_FIELDS as $field) {
            $data[$field] = $setting->getTranslations($field);
        }
        $data['has_smtp_password'] = !empty($setting->smtp_password);
        $data['has_recaptcha_secret_key'] = !empty($setting->recaptcha_secret_key);
        $data['has_turnstile_secret_key'] = !empty($setting->turnstile_secret_key);

        return $this->successResponse($data);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorize('system.manage');

        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'nullable|string|max:255',
            'name.zh-TW' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'yearly_budget_twd' => 'sometimes|numeric|min:0',
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

        $setting = SiteSetting::current();
        $setting->update($validated);

        return $this->successResponse($setting, 'Settings updated');
    }

    public function testSmtp(SiteMailService $mailService): JsonResponse
    {
        $this->authorize('system.manage');

        $setting = SiteSetting::current();

        if (!$mailService->hasSmtpConfig($setting)) {
            return $this->errorResponse('請先設定 SMTP 主機與帳號', 'SMTP_NOT_CONFIGURED', 422);
        }

        if (!$setting->contact_email) {
            return $this->errorResponse('請先設定聯絡信箱（contact_email）作為測試收件人', 'NO_CONTACT_EMAIL', 422);
        }

        try {
            $fromAddress = $setting->smtp_from_address ?? $setting->smtp_username;
            $fromName = $setting->smtp_from_name ?? $setting->name;

            $mailService->getMailer($setting)->raw(
                '這是一封測試信，確認 SMTP 設定正確。',
                function ($message) use ($setting, $fromAddress, $fromName) {
                    $message->to($setting->contact_email)
                            ->from($fromAddress, $fromName)
                            ->subject('SMTP 測試信');
                }
            );

            return $this->successResponse(null, '測試信已發送至 ' . $setting->contact_email);
        } catch (\Throwable $e) {
            return $this->errorResponse('寄信失敗：' . $e->getMessage(), 'SMTP_SEND_FAILED', 422);
        }
    }
}
