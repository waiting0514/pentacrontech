<x-mail::message>
# 新詢價單通知

您收到一筆新的詢價單，請儘速處理。

**訂單編號**：{{ $order->order_number }}

## 客戶資訊

- **姓名**：{{ $order->customer_name }}
- **Email**：{{ $order->customer_email }}
- **公司**：{{ $order->company_name ?: '—' }}
- **備註**：{{ $order->notes ?: '—' }}

## 詢價商品

@foreach($order->orderItems as $item)
- {{ $item->product_name }} × {{ $item->quantity }}
@endforeach

<x-mail::button :url="config('app.url') . '/admin/orders'">
前往後台查看
</x-mail::button>

感謝您，<br>
{{ config('app.name') }}
</x-mail::message>
