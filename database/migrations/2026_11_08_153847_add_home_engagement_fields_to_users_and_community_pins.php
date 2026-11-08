<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('home_checklist_dismissed_at')->nullable()->after('help_note');
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->after('home_checklist_dismissed_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('community_user', function (Blueprint $table) {
            $table->timestamp('pinned_at')->nullable()->after('guidelines_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('community_user', function (Blueprint $table) {
            $table->dropColumn('pinned_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by_user_id');
            $table->dropColumn('home_checklist_dismissed_at');
        });
    }
};
