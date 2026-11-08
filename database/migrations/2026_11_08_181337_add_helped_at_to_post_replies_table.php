<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_replies', function (Blueprint $table) {
            $table->timestamp('helped_at')->nullable()->after('body');
            $table->foreignId('helped_by_user_id')->nullable()->after('helped_at')->constrained('users')->nullOnDelete();

            $table->index(['post_id', 'helped_at']);
        });
    }

    public function down(): void
    {
        Schema::table('post_replies', function (Blueprint $table) {
            $table->dropIndex(['post_id', 'helped_at']);
            $table->dropConstrainedForeignId('helped_by_user_id');
            $table->dropColumn('helped_at');
        });
    }
};
