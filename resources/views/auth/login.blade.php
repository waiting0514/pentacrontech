@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="ix-d-md mb-8">{{ __('frontend.auth.login') }}</h1>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 p-4">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.username') }}</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.password') }}</label>
                <input type="password" name="password" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <button type="submit" class="ix-btn ix-btn-p w-full">
                {{ __('frontend.auth.login') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-steel">
            {{ __('frontend.auth.no_account') }}
            <a href="{{ route('register') }}" class="text-eblue font-semibold hover:underline">{{ __('frontend.auth.register') }}</a>
        </p>
    </div>
</section>
@endsection
