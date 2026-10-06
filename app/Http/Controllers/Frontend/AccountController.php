<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user    = auth()->user()->load('customerProfile');
        $profile = $user->customerProfile;

        return view('account.index', compact('user', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $user->update(['name' => $validated['name']]);

        CustomerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $validated['company_name'],
                'phone'        => $validated['phone'],
                'address'      => $validated['address'],
            ]
        );

        return redirect()->route('account.show')->with('success', __('frontend.account.saved'));
    }
}
