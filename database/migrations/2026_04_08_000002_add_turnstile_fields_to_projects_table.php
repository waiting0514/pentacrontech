<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('captcha_provider', 20)->default('none')->after('recaptcha_secret_key');
            $table->string('turnstile_site_key')->nullable()->after('captcha_provider');
            $table->string('turnstile_secret_key')->nullable()->after('turnstile_site_key');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['captcha_provider', 'turnstile_site_key', 'turnstile_secret_key']);
        });
    }
};
