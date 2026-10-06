@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-12 h-1 bg-[#BE185D] mb-4"></div>
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-black text-[#1a1a1a] tracking-tight">{{ __('frontend.account.orders') }}</h1>
            <a href="{{ route('account.show') }}" class="text-sm text-gray-500 hover:text-[#BE185D]">← {{ __('frontend.account.title') }}</a>
        </div>

        @if ($orders->isEmpty())
            <div class="text-center py-20">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 text-lg mb-6">{{ __('frontend.account.no_orders') }}</p>
                <a href="{{ lroute('products.index') }}" class="inline-flex items-center px-6 py-3 ind-btn rounded">
                    {{ __('frontend.inquiry.go_shop') }}
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ route('account.orders.show', $order) }}"
                       class="block bg-white border border-gray-200 rounded-lg px-6 py-4 hover:border-[#BE185D] transition group">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-[#1a1a1a] group-hover:text-[#BE185D] transition">{{ $order->order_number }}</span>
                                <span class="ml-3 text-sm text-gray-400">{{ $order->created_at->format('Y-m-d') }}</span>
                                <span class="ml-3 text-sm text-gray-500">{{ $order->order_items_count }} {{ __('frontend.account.items') }}</span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold
                                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status === 'completed') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ __('admin.orders.status_' . $order->status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
