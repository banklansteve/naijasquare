<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20);
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'permission_id']);
        });

        $now = now();
        $permissions = [
            'community.update' => 'Update community settings',
            'community.delete' => 'Delete community',
            'community.transfer' => 'Transfer ownership',
            'community.member.approve' => 'Approve join requests',
            'community.member.remove' => 'Remove members',
            'community.member.invite' => 'Invite members',
            'community.moderator.invite' => 'Invite admins',
            'community.moderator.remove' => 'Remove admins',
            'community.guidelines.update' => 'Edit guidelines',
            'community.moderate' => 'Moderate content',
        ];

        $ids = [];
        foreach ($permissions as $key => $label) {
            $ids[$key] = DB::table('permissions')->insertGetId([
                'key' => $key,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $owner = array_keys($permissions);
        $moderator = [
            'community.update',
            'community.member.approve',
            'community.member.remove',
            'community.member.invite',
            'community.moderator.invite',
            'community.guidelines.update',
            'community.moderate',
        ];

        foreach ($owner as $key) {
            DB::table('role_permissions')->insert([
                'role' => 'owner',
                'permission_id' => $ids[$key],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($moderator as $key) {
            DB::table('role_permissions')->insert([
                'role' => 'moderator',
                'permission_id' => $ids[$key],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
