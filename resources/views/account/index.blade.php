@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-12 h-1 bg-[#BE185D] mb-4"></div>
        <h1 class="text-3xl font-black text-[#1a1a1a] mb-2 tracking-tight">{{ __('frontend.account.title') }}</h1>
        <p class="text-gray-500 text-sm mb-8">{{ $user->email }}</p>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded p-4">
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
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('frontend.auth.name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full border border-gray-300 rounded px-4 py-2.5 text-sm focus:outline-none focus:border-[#BE185D]">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('frontend.account.company_name') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name', $profile?->company_name) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2.5 text-sm focus:outline-none focus:border-[#BE185D]">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('frontend.account.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $profile?->phone) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2.5 text-sm focus:outline-none focus:border-[#BE185D]">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('frontend.account.address') }}</label>
                <input type="text" name="address" value="{{ old('address', $profile?->address) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2.5 text-sm focus:outline-none focus:border-[#BE185D]">
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="ind-btn px-6 py-2.5 rounded font-semibold text-sm">
                    {{ __('frontend.account.save') }}
                </button>
                <a href="{{ route('account.orders.index') }}" class="text-sm text-[#BE185D] font-semibold hover:underline">
                    {{ __('frontend.account.orders') }} →
                </a>
            </div>
        </form>
    </div>
</section>
@endsection
