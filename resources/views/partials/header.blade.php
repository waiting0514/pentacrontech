{{-- Site Header --}}
@php
    $site = $site ?? \App\Models\Project::first();
@endphp

<header x-data="{ mobileMenuOpen: false }" class="bg-white sticky top-0 z-50 border-b border-gray-100">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="{{ lroute('home') }}" class="flex items-center">
                    @if($site?->logo_url)
                        <img src="{{ $site->logo_url }}" alt="{{ $site->name }}" style="height: 60px; width: auto;">
                    @else
                        <span class="text-xl font-bold text-gray-900 tracking-tight">{{ $site?->name ?? config('app.name') }}</span>
                    @endif
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ lroute('home') }}" class="@if(request()->is('/') || request()->is('{locale}')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.home') }}
                </a>
                <a href="{{ lroute('products.index') }}" class="@if(request()->is('products*') || request()->is('{locale}/products*') || request()->is('category/*')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.products') }}
                </a>
                <a href="{{ lroute('news.index') }}" class="@if(request()->is('news*') || request()->is('{locale}/news*')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.news') }}
                </a>
                <a href="{{ lroute('knowledge.index') }}" class="@if(request()->is('knowledge*') || request()->is('{locale}/knowledge*')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.knowledge') }}
                </a>
                <a href="{{ lroute('about') }}" class="@if(request()->is('about*') || request()->is('{locale}/about')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.about') }}
                </a>
                <a href="{{ lroute('contact') }}" class="@if(request()->is('contact*') || request()->is('{locale}/contact')) text-[#BE185D] font-semibold @else text-gray-600 hover:text-gray-900 font-medium @endif text-sm transition">
                    {{ __('frontend.nav.contact') }}
                </a>

                {{-- Inquiry Cart --}}
                @if(false)
                <a href="{{ lroute('inquiry.index') }}" class="relative text-gray-500 hover:text-[#BE185D] transition" title="{{ __('frontend.inquiry.title') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span x-show="$store.cart.totalCount() > 0" x-cloak
                          x-text="$store.cart.totalCount()"
                          class="absolute -top-2 -right-2 bg-[#BE185D] text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center" style="font-size: 10px;"></span>
                </a>
                @endif

                {{-- Customer Auth --}}
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 transition text-sm font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ Str::limit(auth()->user()->name, 10) }}
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('account.show') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-[#BE185D] hover:bg-pink-50">{{ __('frontend.account.title') }}</a>
                            <a href="{{ route('account.orders.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-[#BE185D] hover:bg-pink-50">{{ __('frontend.account.orders') }}</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:text-[#BE185D] hover:bg-pink-50">{{ __('frontend.auth.logout') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-900 transition text-sm font-medium">{{ __('frontend.auth.login') }}</a>
                @endauth

                {{-- Language Switcher --}}
                <div class="ml-2">
                    <x-lang-switcher />
                </div>
            </div>

            {{-- Mobile menu button --}}
            <div class="md:hidden flex items-center gap-3">
                {{-- Mobile Cart --}}
                @if(false)
                <a href="{{ lroute('inquiry.index') }}" class="relative text-gray-500 hover:text-[#BE185D] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span x-show="$store.cart.totalCount() > 0" x-cloak
                          x-text="$store.cart.totalCount()"
                          class="absolute -top-2 -right-2 bg-[#BE185D] text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center" style="font-size: 10px;"></span>
                </a>
                @endif
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 hover:text-gray-900 p-2">
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Navigation --}}
        <div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden pb-4 border-t border-gray-100">
            <div class="flex flex-col space-y-1 pt-4">
                <a href="{{ lroute('home') }}" class="@if(request()->is('/') || request()->is('{locale}')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.home') }}</a>
                <a href="{{ lroute('products.index') }}" class="@if(request()->is('products*') || request()->is('{locale}/products*') || request()->is('category/*')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.products') }}</a>
                <a href="{{ lroute('news.index') }}" class="@if(request()->is('news*') || request()->is('{locale}/news*')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.news') }}</a>
                <a href="{{ lroute('knowledge.index') }}" class="@if(request()->is('knowledge*') || request()->is('{locale}/knowledge*')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.knowledge') }}</a>
                <a href="{{ lroute('about') }}" class="@if(request()->is('about*') || request()->is('{locale}/about')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.about') }}</a>
                <a href="{{ lroute('contact') }}" class="@if(request()->is('contact*') || request()->is('{locale}/contact')) text-[#BE185D] bg-pink-50 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2.5 rounded-lg font-medium text-sm transition">{{ __('frontend.nav.contact') }}</a>

                {{-- Mobile Customer Auth --}}
                <div class="pt-2 mt-2 border-t border-gray-100">
                    @auth
                        <a href="{{ route('account.show') }}" class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm font-medium">{{ __('frontend.account.title') }}</a>
                        <a href="{{ route('account.orders.index') }}" class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm font-medium">{{ __('frontend.account.orders') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm font-medium">{{ __('frontend.auth.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm font-medium">{{ __('frontend.auth.login') }}</a>
                        <a href="{{ route('register') }}" class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm font-medium">{{ __('frontend.auth.register') }}</a>
                    @endauth
                </div>
                <div class="pt-2 border-t border-gray-100 px-4">
                    <x-lang-switcher />
                </div>
            </div>
        </div>
    </nav>
</header>
