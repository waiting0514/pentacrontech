<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('title');                       // {"zh-TW": "...", "en": "..."}
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_attachment_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->constrained('product_attachments')->cascadeOnDelete();
            $table->string('locale', 20);
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->unique(['attachment_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attachment_files');
        Schema::dropIfExists('product_attachments');
    }
};
