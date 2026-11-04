<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('frontend.maintenance.title') }} | {{ $site?->name ?? config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">

    @vite(['resources/css/app.css'])
</head>
<body class="antialiased">
    <div class="min-h-screen flex items-center justify-center bg-navy">
        <div class="max-w-lg mx-auto px-6 text-center">
            {{-- Maintenance Icon --}}
            <div class="mb-8">
                <svg class="w-24 h-24 mx-auto text-pblue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>

            {{-- Message --}}
            <h1 class="ix-d-md text-white mb-4">
                {{ __('frontend.maintenance.title') }}
            </h1>

            <p class="ix-body-lg text-[#A9B6C4] mb-8">
                {{ $message ?? __('frontend.maintenance.default_message') }}
            </p>

            {{-- Contact Info --}}
            @if($site?->contact_email || $site?->contact_phone)
            <div class="text-[#A9B6C4]">
                <p class="mb-2">{{ __('frontend.maintenance.contact_urgent') }}</p>
                @if($site->contact_email)
                <a href="mailto:{{ $site->contact_email }}" class="text-plight hover:text-white">
                    {{ $site->contact_email }}
                </a>
                @endif
                @if($site->contact_phone)
                <span class="mx-2">|</span>
                <a href="tel:{{ $site->contact_phone }}" class="text-plight hover:text-white">
                    {{ $site->contact_phone }}
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</body>
</html>
