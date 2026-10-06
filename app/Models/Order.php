<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'order_number',
        'customer_name',
        'customer_email',
        'company_name',
        'customer_phone',
        'customer_address',
        'notes',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    protected static function generateOrderNumber(): string
    {
        return DB::transaction(function () {
            $today = now()->format('Ymd');
            $prefix = "INQ-{$today}-";

            $last = static::where('order_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->count();

            $sequence = str_pad($last + 1, 4, '0', STR_PAD_LEFT);

            return $prefix . $sequence;
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
