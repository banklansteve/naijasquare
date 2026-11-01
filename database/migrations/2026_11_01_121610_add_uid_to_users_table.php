<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('uid', 8)->nullable()->unique()->after('id');
        });

        foreach (DB::table('users')->orderBy('id')->get(['id']) as $user) {
            do {
                $uid = Str::upper(Str::random(8));
            } while (DB::table('users')->where('uid', $uid)->exists());

            DB::table('users')->where('id', $user->id)->update(['uid' => $uid]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['uid']);
            $table->dropColumn('uid');
        });
    }
};
