<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = ['recaptcha_secret_key', 'turnstile_secret_key'];

    public function up(): void
    {
        // 密文長度會超過 varchar(255)
        Schema::table('projects', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                $table->text($column)->nullable()->change();
            }
        });

        // 既有明文改存密文（以 raw 查詢繞過 encrypted cast）
        DB::table('projects')->get(['id', ...$this->columns])->each(function ($row) {
            $updates = [];
            foreach ($this->columns as $column) {
                if (!empty($row->{$column})) {
                    $updates[$column] = Crypt::encryptString($row->{$column});
                }
            }
            if ($updates) {
                DB::table('projects')->where('id', $row->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        DB::table('projects')->get(['id', ...$this->columns])->each(function ($row) {
            $updates = [];
            foreach ($this->columns as $column) {
                if (!empty($row->{$column})) {
                    $updates[$column] = Crypt::decryptString($row->{$column});
                }
            }
            if ($updates) {
                DB::table('projects')->where('id', $row->id)->update($updates);
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                $table->string($column)->nullable()->change();
            }
        });
    }
};
