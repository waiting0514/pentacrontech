<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class ProjectMailService
{
    public function getMailer(Project $project): Mailer
    {
        if (!$project->smtp_host || !$project->smtp_username) {
            return Mail::mailer();
        }

        $mailerName = 'project_' . $project->id;

        config(["mail.mailers.{$mailerName}" => [
            'transport' => 'smtp',
            'host' => $project->smtp_host,
            'port' => $project->smtp_port ?? 587,
            'encryption' => $project->smtp_encryption ?? 'tls',
            'username' => $project->smtp_username,
            'password' => $project->smtp_password,
            'timeout' => 10,
        ]]);

        return Mail::mailer($mailerName);
    }

    public function send(Project $project, string $to, Mailable $mailable): void
    {
        $fromAddress = $project->smtp_from_address ?? $project->smtp_username ?? config('mail.from.address');
        $fromName = $project->smtp_from_name ?? $project->name ?? config('mail.from.name');

        $this->getMailer($project)
            ->to($to)
            ->send($mailable->from($fromAddress, $fromName));
    }

    public function hasSmtpConfig(Project $project): bool
    {
        return !empty($project->smtp_host) && !empty($project->smtp_username);
    }
}
