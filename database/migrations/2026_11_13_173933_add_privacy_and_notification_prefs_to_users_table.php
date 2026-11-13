<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_visibility', 24)->default('communities')->after('location_display');
            $table->string('help_visibility', 24)->default('communities')->after('help_note');
            $table->boolean('notify_needs_replies')->default(true)->after('help_visibility');
            $table->boolean('notify_community')->default(true)->after('notify_needs_replies');
            $table->boolean('notify_admin')->default(true)->after('notify_community');
            $table->boolean('prefer_product_emails')->default(false)->after('notify_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_visibility',
                'help_visibility',
                'notify_needs_replies',
                'notify_community',
                'notify_admin',
                'prefer_product_emails',
            ]);
        });
    }
};
