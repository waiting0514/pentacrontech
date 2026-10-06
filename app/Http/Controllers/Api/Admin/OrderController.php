<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('order.view');

        $query = Order::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15);

        return $this->successResponse($orders);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('order.view');

        $order = Order::with('orderItems')->findOrFail($id);

        $data = $order->toArray();
        $data['order_items'] = $order->orderItems->map(fn ($item) => [
            'id'           => $item->id,
            'product_id'   => $item->product_id,
            'product_name' => $item->product_name,
            'price'        => $item->price,
            'quantity'     => $item->quantity,
            'subtotal'     => bcmul($item->price, $item->quantity, 2),
        ]);
        $data['total'] = $order->orderItems->sum(fn ($item) => $item->price * $item->quantity);

        return $this->successResponse($data);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->authorize('order.view');

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return $this->successResponse($order->fresh());
    }
}
