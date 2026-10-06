<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'feature',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'display_cost_twd',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_usd' => 'decimal:6',
        'display_cost_twd' => 'decimal:2',
    ];

    /**
     * Get the user that owns the AI usage log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
