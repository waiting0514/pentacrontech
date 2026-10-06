<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaptchaService
{
    public static function getSiteKey(Project $project): ?string
    {
        return match ($project->captcha_provider) {
            'recaptcha' => $project->recaptcha_site_key,
            'turnstile' => $project->turnstile_site_key,
            default => null,
        };
    }

    public static function verify(Project $project, Request $request): bool
    {
        $provider = $project->captcha_provider ?? 'none';

        if ($provider === 'none') {
            return true;
        }

        if ($provider === 'recaptcha') {
            $token = $request->input('g-recaptcha-response');
            if (!$token || !$project->recaptcha_secret_key) {
                return false;
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $project->recaptcha_secret_key,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return $response->json('success', false);
        }

        if ($provider === 'turnstile') {
            $token = $request->input('cf-turnstile-response');
            if (!$token || !$project->turnstile_secret_key) {
                return false;
            }

            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $project->turnstile_secret_key,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return $response->json('success', false);
        }

        return true;
    }
}
