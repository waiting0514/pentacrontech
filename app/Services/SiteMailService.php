<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SiteMailService
{
    public function getMailer(SiteSetting $setting): Mailer
    {
        if (!$setting->smtp_host || !$setting->smtp_username) {
            return Mail::mailer();
        }

        $mailerName = 'site_smtp';

        config(["mail.mailers.{$mailerName}" => [
            'transport' => 'smtp',
            'host' => $setting->smtp_host,
            'port' => $setting->smtp_port ?? 587,
            'encryption' => $setting->smtp_encryption ?? 'tls',
            'username' => $setting->smtp_username,
            'password' => $setting->smtp_password,
            'timeout' => 10,
        ]]);

        return Mail::mailer($mailerName);
    }

    public function send(SiteSetting $setting, string $to, Mailable $mailable): void
    {
        $fromAddress = $setting->smtp_from_address ?? $setting->smtp_username ?? config('mail.from.address');
        $fromName = $setting->smtp_from_name ?? $setting->name ?? config('mail.from.name');

        $this->getMailer($setting)
            ->to($to)
            ->send($mailable->from($fromAddress, $fromName));
    }

    public function hasSmtpConfig(SiteSetting $setting): bool
    {
        return !empty($setting->smtp_host) && !empty($setting->smtp_username);
    }
}
