<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InquirySubmittedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CaptchaService;
use App\Services\SiteMailService;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $site    = SiteSetting::current();
        $profile = auth()->check() ? auth()->user()->customerProfile : null;

        return view('inquiry.index', [
            'site'            => $site,
            'profile'         => $profile,
            'metaTitle'       => __('frontend.inquiry.title'),
            'metaDescription' => __('frontend.inquiry.title'),
            'captchaProvider' => $site?->captcha_provider ?? 'none',
            'captchaSiteKey' => $site ? CaptchaService::getSiteKey($site) : null,
        ]);
    }

    public function success(): View
    {
        $site = SiteSetting::current();

        return view('inquiry.success', [
            'site' => $site,
            'metaTitle' => __('frontend.inquiry.success_title'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // CAPTCHA verification
        $site = SiteSetting::current();
        if ($site && !CaptchaService::verify($site, $request)) {
            return back()->withInput()->withErrors(['captcha' => __('frontend.contact.captcha_error')]);
        }

        $validated = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'company_name'      => 'nullable|string|max:255',
            'customer_phone'    => 'nullable|string|max:50',
            'customer_address'  => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:2000',
            'cart_items'     => 'required|string',
        ]);

        $cartItems = json_decode($validated['cart_items'], true);

        if (empty($cartItems) || ! is_array($cartItems)) {
            return back()->withErrors(['cart_items' => __('frontend.inquiry.cart_empty')])->withInput();
        }

        $order = DB::transaction(function () use ($validated, $cartItems) {
            $order = Order::create([
                'customer_id'      => auth()->id(),
                'customer_name'    => $validated['customer_name'],
                'customer_email'   => $validated['customer_email'],
                'company_name'     => $validated['company_name'] ?? null,
                'customer_phone'   => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'pending',
            ]);

            foreach ($cartItems as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::find($productId) : null;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $productId,
                    'product_name' => $product ? ($product->name ?? $item['name'] ?? '') : ($item['name'] ?? ''),
                    'price'        => $product ? $product->price : 0,
                    'quantity'     => max(1, (int) ($item['quantity'] ?? 1)),
                ]);
            }

            return $order;
        });

        try {
            $site = SiteSetting::current();
            if ($site?->contact_email) {
                app(SiteMailService::class)->send($site, $site->contact_email, new InquirySubmittedMail($order));
            }
        } catch (\Throwable) {
            // SMTP not configured — silently ignore
        }

        return redirect()->route('inquiry.success');
    }
}
