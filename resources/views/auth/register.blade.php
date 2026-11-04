@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="ix-d-md mb-8">{{ __('frontend.auth.register') }}</h1>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 p-4">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.username') }} <span class="text-red-600">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}" required
                    class="w-full ind-input px-4 py-2.5 text-sm"
                    pattern="[a-zA-Z0-9_]+" title="{{ __('frontend.auth.username_hint') }}">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.name') }} <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.email') }} <span class="text-red-600">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.password') }} <span class="text-red-600">*</span></label>
                <input type="password" name="password" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.password_confirmation') }} <span class="text-red-600">*</span></label>
                <input type="password" name="password_confirmation" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <button type="submit" class="ix-btn ix-btn-p w-full">
                {{ __('frontend.auth.register') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-steel">
            {{ __('frontend.auth.already_have_account') }}
            <a href="{{ route('login') }}" class="text-eblue font-semibold hover:underline">{{ __('frontend.auth.login') }}</a>
        </p>
    </div>
</section>
@endsection
