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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');

            // Content
            $table->json('name');           // {"zh-TW": "...", "en": "..."}
            $table->json('slug');           // {"zh-TW": "...", "en": "..."}
            $table->json('description');    // {"zh-TW": "...", "en": "..."}
            $table->text('specs')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_inquiry_only')->default(false);

            // SEO Fields (AI Generated)
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            $table->timestamps();

            // Index for SEO-friendly URL lookup
            $table->index(['id']);

            // Note: Unique constraints on JSON columns require virtual columns in MySQL.
            // For development simplicity, we handle unique logic in Application layer/Model.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};