@extends('layouts.app')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold">{{ __('frontend.inquiry.title') }}</li>
        </ol>
    </div>
</div>

<section class="py-12" x-data="{ toast: false }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-12 h-1 bg-[#BE185D] mb-4"></div>
        <h1 class="text-3xl font-black text-[#1a1a1a] mb-8 tracking-tight">{{ __('frontend.inquiry.title') }}</h1>

        {{-- Empty Cart --}}
        <div x-show="$store.cart.totalCount() === 0" x-cloak class="text-center py-20">
            <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 text-lg mb-6">{{ __('frontend.inquiry.cart_empty') }}</p>
            <a href="{{ lroute('products.index') }}" class="inline-flex items-center px-6 py-3 ind-btn rounded">
                {{ __('frontend.inquiry.go_shop') }}
            </a>
        </div>

        {{-- Cart with items --}}
        <div x-show="$store.cart.totalCount() > 0" x-cloak>
            {{-- Cart Items --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-8">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-6 py-3 text-sm font-bold text-gray-700">{{ __('frontend.inquiry.product') }}</th>
                            <th class="text-center px-4 py-3 text-sm font-bold text-gray-700 w-36">{{ __('frontend.inquiry.quantity') }}</th>
                            <th class="text-right px-6 py-3 text-sm font-bold text-gray-700 w-32">{{ __('frontend.inquiry.subtotal') }}</th>
                            <th class="w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in $store.cart.items" :key="index">
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img :src="item.image || ''" x-show="item.image" class="w-12 h-12 object-cover rounded border border-gray-200">
                                        <span class="font-semibold text-[#1a1a1a]" x-text="item.name"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="$store.cart.updateQuantity(index, item.quantity - 1)"
                                                class="w-7 h-7 rounded border border-gray-300 hover:border-[#BE185D] flex items-center justify-center text-gray-600 hover:text-[#BE185D] transition">−</button>
                                        <span class="w-8 text-center font-bold" x-text="item.quantity"></span>
                                        <button @click="$store.cart.updateQuantity(index, item.quantity + 1)"
                                                class="w-7 h-7 rounded border border-gray-300 hover:border-[#BE185D] flex items-center justify-center text-gray-600 hover:text-[#BE185D] transition">+</button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span x-show="item.price > 0" class="font-black text-[#BE185D]"
                                          x-text="'NT$ ' + Number(item.price * item.quantity).toLocaleString()"></span>
                                    <span x-show="!item.price || item.price == 0" class="text-gray-500 font-semibold">{{ __('frontend.product.inquiry_welcome') }}</span>
                                </td>
                                <td class="pr-4 py-4 text-center">
                                    <button @click="$store.cart.remove(index)"
                                            class="text-gray-400 hover:text-red-500 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Contact Form --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-black text-[#1a1a1a] mb-6 uppercase tracking-wide">{{ __('frontend.inquiry.contact_info') }}</h2>

                @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('inquiry.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    {{-- Hidden cart data --}}
                    <input type="hidden" name="cart_items" :value="JSON.stringify($store.cart.items)">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ __('frontend.inquiry.customer_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ __('frontend.inquiry.customer_email') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ __('frontend.inquiry.company_name') }}
                            </label>
                            <input type="text" name="company_name" value="{{ old('company_name', $profile?->company_name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ __('frontend.inquiry.customer_phone') }}
                            </label>
                            <input type="tel" name="customer_phone" value="{{ old('customer_phone', $profile?->phone) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            {{ __('frontend.inquiry.customer_address') }}
                        </label>
                        <input type="text" name="customer_address" value="{{ old('customer_address', $profile?->address) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            {{ __('frontend.inquiry.notes') }}
                        </label>
                        <textarea name="notes" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#BE185D] transition resize-none">{{ old('notes') }}</textarea>
                    </div>

                    {{-- CAPTCHA --}}
                    <x-captcha :provider="$captchaProvider" :site-key="$captchaSiteKey" />

                    <button type="submit" :disabled="submitting"
                            class="w-full md:w-auto inline-flex items-center justify-center px-8 py-4 ind-btn rounded text-lg font-bold disabled:opacity-60 disabled:cursor-not-allowed">
                        {{-- Loading spinner --}}
                        <svg x-show="submitting" x-cloak class="animate-spin mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        {{-- Send icon --}}
                        <svg x-show="!submitting" class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="submitting ? '{{ __('frontend.inquiry.submitting') }}' : '{{ __('frontend.inquiry.submit') }}'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

<x-captcha-script :provider="$captchaProvider" :site-key="$captchaSiteKey" />
