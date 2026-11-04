@extends('layouts.app')

@section('content')
<section class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="ix-d-md">{{ $order->order_number }}</h1>
                <p class="text-sm text-muted mt-1">{{ $order->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold
                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                    @elseif($order->status === 'completed') bg-green-100 text-green-800
                    @else bg-lsteel text-steel @endif">
                    {{ __('admin.orders.status_' . $order->status) }}
                </span>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white border border-line p-6 mb-6">
            <h2 class="font-mono text-xs font-medium text-steel uppercase tracking-[.12em] mb-4">{{ __('frontend.account.contact_info') }}</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-muted">{{ __('frontend.auth.name') }}</dt>
                    <dd class="font-semibold text-ink">{{ $order->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Email</dt>
                    <dd class="font-semibold text-ink">{{ $order->customer_email }}</dd>
                </div>
                @if($order->company_name)
                <div>
                    <dt class="text-muted">{{ __('frontend.account.company_name') }}</dt>
                    <dd class="font-semibold text-ink">{{ $order->company_name }}</dd>
                </div>
                @endif
                @if($order->customer_phone)
                <div>
                    <dt class="text-muted">{{ __('frontend.account.phone') }}</dt>
                    <dd class="font-semibold text-ink">{{ $order->customer_phone }}</dd>
                </div>
                @endif
                @if($order->notes)
                <div class="col-span-2">
                    <dt class="text-muted">{{ __('frontend.inquiry.notes') }}</dt>
                    <dd class="font-semibold text-ink">{{ $order->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Order Items --}}
        <div class="bg-white border border-line overflow-hidden mb-6">
            <h2 class="font-mono text-xs font-medium text-steel uppercase tracking-[.12em] px-6 py-4 border-b border-line">{{ __('frontend.account.order_items') }}</h2>
            <table class="w-full text-sm">
                <thead class="bg-off">
                    <tr>
                        <th class="text-left px-6 py-3 text-steel font-medium">{{ __('frontend.account.product') }}</th>
                        <th class="text-center px-4 py-3 text-steel font-medium">{{ __('frontend.inquiry.quantity') }}</th>
                        <th class="text-right px-6 py-3 text-steel font-medium">{{ __('frontend.account.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-ink">{{ $item->product_name }}</td>
                            <td class="px-4 py-4 text-center text-steel">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-ink">
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
                <tfoot class="bg-off">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-bold text-ink">{{ __('admin.orders.total') }}</td>
                        <td class="px-6 py-4 text-right font-black text-ink">NT$ {{ number_format($total) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <a href="{{ route('account.orders.index') }}" class="text-sm text-steel hover:text-eblue">← {{ __('frontend.account.orders') }}</a>
    </div>
</section>
@endsection
