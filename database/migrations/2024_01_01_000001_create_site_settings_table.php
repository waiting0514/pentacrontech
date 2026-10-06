<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description')->nullable();

            // Budget
            $table->decimal('yearly_budget_twd', 10, 2)->default(1000.00);
            $table->decimal('yearly_usage_twd', 10, 2)->default(0.00);
            $table->boolean('is_ai_enabled')->default(true);
            $table->boolean('product_default_inquiry_mode')->default(false);
            
            // SEO Meta Tags
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Maintenance Mode
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            
            // Global Site Settings
            $table->string('logo_url')->nullable();
            $table->json('social_links')->nullable();
            
            // Contact Info (for LocalBusiness Schema and display)
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();
            $table->decimal('geo_lat', 10, 7)->nullable();
            $table->decimal('geo_lng', 10, 7)->nullable();
            
            // Google reCAPTCHA
            $table->string('recaptcha_site_key')->nullable();
            $table->text('recaptcha_secret_key')->nullable(); // 加密儲存，密文長度超過 varchar(255)

            // Captcha provider / Cloudflare Turnstile
            $table->string('captcha_provider', 20)->default('none');
            $table->string('turnstile_site_key')->nullable();
            $table->text('turnstile_secret_key')->nullable(); // 加密儲存

            // SMTP
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 10)->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_from_address')->nullable();
            $table->string('smtp_from_name')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
