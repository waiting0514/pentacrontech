<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaptchaService
{
    public static function getSiteKey(SiteSetting $setting): ?string
    {
        return match ($setting->captcha_provider) {
            'recaptcha' => $setting->recaptcha_site_key,
            'turnstile' => $setting->turnstile_site_key,
            default => null,
        };
    }

    public static function verify(SiteSetting $setting, Request $request): bool
    {
        $provider = $setting->captcha_provider ?? 'none';

        if ($provider === 'none') {
            return true;
        }

        if ($provider === 'recaptcha') {
            $token = $request->input('g-recaptcha-response');
            if (!$token || !$setting->recaptcha_secret_key) {
                return false;
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $setting->recaptcha_secret_key,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return $response->json('success', false);
        }

        if ($provider === 'turnstile') {
            $token = $request->input('cf-turnstile-response');
            if (!$token || !$setting->turnstile_secret_key) {
                return false;
            }

            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $setting->turnstile_secret_key,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return $response->json('success', false);
        }

        return true;
    }
}
