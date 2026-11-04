@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="ix-d-md mb-2">{{ __('frontend.account.title') }}</h1>
        <p class="text-steel text-sm mb-8">{{ $user->email }}</p>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 p-4">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.update') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.auth.name') }} <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.account.company_name') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name', $profile?->company_name) }}"
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.account.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $profile?->phone) }}"
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block font-mono text-xs uppercase tracking-[.12em] text-steel mb-2">{{ __('frontend.account.address') }}</label>
                <input type="text" name="address" value="{{ old('address', $profile?->address) }}"
                    class="w-full ind-input px-4 py-2.5 text-sm">
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="ix-btn ix-btn-p ix-btn-sm">
                    {{ __('frontend.account.save') }}
                </button>
                <a href="{{ route('account.orders.index') }}" class="text-sm text-eblue font-semibold hover:underline">
                    {{ __('frontend.account.orders') }} →
                </a>
            </div>
        </form>
    </div>
</section>
@endsection
