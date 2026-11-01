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
        Schema::table('communities', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('access_mode', 20)->default('request')->after('listed');
            $table->string('location_type', 20)->default('city')->after('access_mode');
            $table->string('location_label')->nullable()->after('location_type');
            $table->json('tag_keys')->nullable()->after('interest_keys');
            $table->text('guidelines')->nullable()->after('tag_keys');
            $table->boolean('require_guidelines_accept')->default(false)->after('guidelines');
            $table->string('who_can_post', 20)->default('members')->after('require_guidelines_accept');
            $table->string('member_list_visibility', 20)->default('members')->after('who_can_post');
            $table->string('cover_path')->nullable()->after('member_list_visibility');
            $table->boolean('admin_digest_enabled')->default(false)->after('cover_path');
            $table->foreignId('created_by')->nullable()->after('admin_digest_enabled')->constrained('users')->nullOnDelete();
        });

        Schema::table('community_user', function (Blueprint $table) {
            $table->string('role', 20)->default('member')->after('user_id');
            $table->timestamp('guidelines_accepted_at')->nullable()->after('role');
        });

        foreach (DB::table('communities')->orderBy('id')->get(['id', 'name', 'city']) as $community) {
            $base = Str::slug($community->name) ?: 'community-'.$community->id;
            $slug = $base;
            $n = 1;
            while (DB::table('communities')->where('slug', $slug)->where('id', '!=', $community->id)->exists()) {
                $slug = $base.'-'.$n;
                $n++;
            }

            DB::table('communities')->where('id', $community->id)->update([
                'slug' => $slug,
                'access_mode' => 'request',
                'location_type' => 'city',
                'location_label' => $community->city,
            ]);
        }

        Schema::create('community_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('note', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['community_id', 'user_id']);
        });

        Schema::create('community_admin_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_admin_invitations');
        Schema::dropIfExists('community_join_requests');

        Schema::table('community_user', function (Blueprint $table) {
            $table->dropColumn(['role', 'guidelines_accepted_at']);
        });

        Schema::table('communities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'slug',
                'access_mode',
                'location_type',
                'location_label',
                'tag_keys',
                'guidelines',
                'require_guidelines_accept',
                'who_can_post',
                'member_list_visibility',
                'cover_path',
                'admin_digest_enabled',
            ]);
        });
    }
};
