<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['monthly_budget_usd', 'monthly_usage_usd']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('yearly_budget_twd', 10, 2)->default(1000.00)->after('description');
            $table->decimal('yearly_usage_twd', 10, 2)->default(0.00)->after('yearly_budget_twd');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['yearly_budget_twd', 'yearly_usage_twd']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('monthly_budget_usd', 10, 2)->default(100.00)->after('description');
            $table->decimal('monthly_usage_usd', 10, 2)->default(0.00)->after('monthly_budget_usd');
        });
    }
};
