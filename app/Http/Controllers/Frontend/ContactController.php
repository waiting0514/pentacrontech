<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\CaptchaService;
use App\Services\ProjectMailService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Display contact page.
     */
    public function index(): View
    {
        $site = Project::first();

        return view('pages.contact', [
            'site' => $site,
            'metaTitle' => __('frontend.contact.title'),
            'metaDescription' => __('frontend.contact.meta_description'),
            'captchaProvider' => $site?->captcha_provider ?? 'none',
            'captchaSiteKey' => $site ? CaptchaService::getSiteKey($site) : null,
        ]);
    }

    /**
     * Handle contact form submission.
     */
    public function submit(Request $request): RedirectResponse
    {
        $site = Project::first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        // CAPTCHA verification
        if ($site && !CaptchaService::verify($site, $request)) {
            return back()->withInput()->withErrors(['captcha' => __('frontend.contact.captcha_error')]);
        }

        // Send email notification
        try {
            if ($site?->contact_email) {
                $mailService = app(ProjectMailService::class);
                $fromAddress = $site->smtp_from_address ?? $site->smtp_username ?? config('mail.from.address');
                $fromName = $site->smtp_from_name ?? $site->name ?? config('mail.from.name');

                $mailService->getMailer($site)->raw(
                    "聯絡表單通知\n\n姓名：{$validated['name']}\nEmail：{$validated['email']}\n電話：" . ($validated['phone'] ?? '未提供') . "\n\n訊息內容：\n{$validated['message']}",
                    function ($message) use ($site, $fromAddress, $fromName) {
                        $message->to($site->contact_email)
                                ->from($fromAddress, $fromName)
                                ->subject('網站聯絡表單通知');
                    }
                );
            }
        } catch (\Throwable) {
            // SMTP not configured — silently ignore
        }

        return back()->with('success', __('frontend.contact.success'));
    }
}
