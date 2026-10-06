<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class SiteSetting extends Model
{
    use LogsActivity, HasTranslations;

    public $translatable = [
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'maintenance_message',
        'contact_address',
    ];

    protected $table = 'site_settings';

    protected $fillable = [
        'name',
        'description',
        'yearly_budget_twd',
        'yearly_usage_twd',
        'is_ai_enabled',
        'product_default_inquiry_mode',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'maintenance_mode',
        'maintenance_message',
        // Global Site Settings
        'logo_url',
        'social_links',
        // Contact Info (for LocalBusiness Schema and display)
        'contact_email',
        'contact_phone',
        'contact_address',
        'geo_lat',
        'geo_lng',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'captcha_provider',
        'turnstile_site_key',
        'turnstile_secret_key',
        // SMTP Settings
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $hidden = [
        'smtp_password',
        'recaptcha_secret_key',
        'turnstile_secret_key',
    ];

    protected $casts = [
        'yearly_budget_twd' => 'decimal:2',
        'yearly_usage_twd' => 'decimal:2',
        'is_ai_enabled' => 'boolean',
        'product_default_inquiry_mode' => 'boolean',
        'maintenance_mode' => 'boolean',
        'social_links' => 'array',
        'geo_lat' => 'decimal:7',
        'geo_lng' => 'decimal:7',
        'smtp_password' => 'encrypted',
        'recaptcha_secret_key' => 'encrypted',
        'turnstile_secret_key' => 'encrypted',
        'smtp_port' => 'integer',
    ];

    /**
     * 取得全站唯一的設定列；尚未建立時以預設值建立。
     */
    public static function current(): static
    {
        return static::query()->firstOrCreate([], ['name' => config('app.name')]);
    }

    /**
     * Get the remaining budget.
     */
    public function getRemainingBudgetAttribute(): float
    {
        return $this->yearly_budget_twd - $this->yearly_usage_twd;
    }

    /**
     * Get the usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->yearly_budget_twd == 0) {
            return 0;
        }

        return ($this->yearly_usage_twd / $this->yearly_budget_twd) * 100;
    }

    /**
     * Check if budget is exceeded.
     */
    public function isBudgetExceeded(): bool
    {
        return $this->yearly_usage_twd >= $this->yearly_budget_twd;
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'yearly_budget_twd'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
