<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('meta_title')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('meta_title')->nullable()->change();
            $table->string('meta_keywords')->nullable()->change();
        });
    }
};
