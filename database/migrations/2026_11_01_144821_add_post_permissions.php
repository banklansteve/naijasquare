<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            'post.create' => 'Create a Need or Offer',
            'post.reply' => 'Reply to a post',
            'post.update_own' => 'Edit own posts',
            'post.delete_own' => 'Delete own posts',
            'post.resolve_own' => 'Mark own Needs as resolved',
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

        foreach (['owner', 'moderator', 'member'] as $role) {
            foreach ($ids as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role' => $role,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'post.create',
            'post.reply',
            'post.update_own',
            'post.delete_own',
            'post.resolve_own',
        ];

        $ids = DB::table('permissions')->whereIn('key', $keys)->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
