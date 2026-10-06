<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => __('frontend.auth.inactive'),
                ])->withInput($request->only('username'));
            }

            if (! $user->hasRole('customer')) {
                Auth::logout();
                return back()->withErrors([
                    'username' => __('frontend.auth.not_a_customer'),
                ])->withInput($request->only('username'));
            }

            $request->session()->regenerate();

            return redirect()->intended(route('account.show'));
        }

        return back()->withErrors([
            'username' => __('frontend.auth.failed'),
        ])->withInput($request->only('username'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
