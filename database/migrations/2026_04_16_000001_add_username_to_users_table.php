<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('name');
        });

        // Backfill existing users: use email prefix, append suffix on duplicates
        $users = DB::table('users')->orderBy('id')->get();
        $usedUsernames = [];

        foreach ($users as $user) {
            $base = strstr($user->email, '@', true) ?: 'user';
            $username = $base;
            $counter = 2;

            while (in_array($username, $usedUsernames)) {
                $username = $base . '_' . $counter;
                $counter++;
            }

            $usedUsernames[] = $username;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
