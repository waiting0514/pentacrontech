@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="w-12 h-1 bg-[#BE185D] mb-3"></div>
                <h1 class="text-2xl font-black text-[#1a1a1a] tracking-tight font-mono">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-400 mt-1">{{ $order->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded text-sm font-semibold
                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                    @elseif($order->status === 'completed') bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ __('admin.orders.status_' . $order->status) }}
                </span>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">{{ __('frontend.account.contact_info') }}</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-400">{{ __('frontend.auth.name') }}</dt>
                    <dd class="font-semibold text-[#1a1a1a]">{{ $order->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Email</dt>
                    <dd class="font-semibold text-[#1a1a1a]">{{ $order->customer_email }}</dd>
                </div>
                @if($order->company_name)
                <div>
                    <dt class="text-gray-400">{{ __('frontend.account.company_name') }}</dt>
                    <dd class="font-semibold text-[#1a1a1a]">{{ $order->company_name }}</dd>
                </div>
                @endif
                @if($order->customer_phone)
                <div>
                    <dt class="text-gray-400">{{ __('frontend.account.phone') }}</dt>
                    <dd class="font-semibold text-[#1a1a1a]">{{ $order->customer_phone }}</dd>
                </div>
                @endif
                @if($order->notes)
                <div class="col-span-2">
                    <dt class="text-gray-400">{{ __('frontend.inquiry.notes') }}</dt>
                    <dd class="font-semibold text-[#1a1a1a]">{{ $order->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Order Items --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide px-6 py-4 border-b border-gray-100">{{ __('frontend.account.order_items') }}</h2>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium">{{ __('frontend.account.product') }}</th>
                        <th class="text-center px-4 py-3 text-gray-500 font-medium">{{ __('frontend.inquiry.quantity') }}</th>
                        <th class="text-right px-6 py-3 text-gray-500 font-medium">{{ __('frontend.account.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-[#1a1a1a]">{{ $item->product_name }}</td>
                            <td class="px-4 py-4 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-gray-700">
                                @if($item->price > 0)
                                    NT$ {{ number_format($item->price * $item->quantity) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @if($total > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-bold text-gray-700">{{ __('admin.orders.total') }}</td>
                        <td class="px-6 py-4 text-right font-black text-[#1a1a1a]">NT$ {{ number_format($total) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <a href="{{ route('account.orders.index') }}" class="text-sm text-gray-500 hover:text-[#BE185D]">← {{ __('frontend.account.orders') }}</a>
    </div>
</section>
@endsection
