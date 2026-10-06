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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Language code (e.g. en, zh-TW)');
            $table->string('name')->comment('Display name (e.g. English, 繁體中文)');
            $table->boolean('is_default')->default(false)->comment('Is this the default language?');
            $table->boolean('is_active')->default(true)->comment('Is this language enabled?');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
