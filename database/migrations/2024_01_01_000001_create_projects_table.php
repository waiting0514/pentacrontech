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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->text('description')->nullable();
            
            // Budget
            $table->decimal('monthly_budget_usd', 10, 2)->default(100.00);
            $table->decimal('monthly_usage_usd', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_ai_enabled')->default(true);
            $table->boolean('product_default_inquiry_mode')->default(false);
            
            // SEO Meta Tags
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
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
            $table->string('recaptcha_secret_key')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
