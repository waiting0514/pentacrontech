@extends('layouts.app')

@section('content')
<section class="py-24">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <div class="w-12 h-1 bg-[#BE185D] mx-auto mb-4"></div>
        <h1 class="text-3xl font-black text-[#1a1a1a] mb-4 tracking-tight">{{ __('frontend.inquiry.success_title') }}</h1>
        <p class="text-gray-600 text-lg mb-8">{{ __('frontend.inquiry.success_message') }}</p>

        <a href="{{ lroute('products.index') }}" class="inline-flex items-center px-8 py-4 ind-btn rounded text-lg">
            {{ __('frontend.inquiry.go_shop') }}
        </a>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Clear cart after successful submission
        Alpine.store('cart').clear();
    });
</script>
@endpush
