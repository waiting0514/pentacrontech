<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    public function index(): View
    {
        $orders = Order::where('customer_id', auth()->id())
            ->withCount('orderItems')
            ->latest()
            ->paginate(15);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load('orderItems');
        $total = $order->orderItems->sum(fn ($item) => $item->price * $item->quantity);

        return view('account.orders.show', compact('order', 'total'));
    }
}
