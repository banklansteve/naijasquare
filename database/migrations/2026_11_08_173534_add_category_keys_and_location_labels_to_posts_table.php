<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('category_keys')->nullable()->after('category_key');
            $table->json('location_labels')->nullable()->after('location_label');
        });

        DB::table('posts')->orderBy('id')->chunkById(100, function ($posts): void {
            foreach ($posts as $post) {
                $categoryKeys = filled($post->category_key) ? [$post->category_key] : [];
                $locationLabels = filled($post->location_label) ? [$post->location_label] : [];

                DB::table('posts')->where('id', $post->id)->update([
                    'category_keys' => json_encode(array_values($categoryKeys)),
                    'location_labels' => json_encode(array_values($locationLabels)),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['category_keys', 'location_labels']);
        });
    }
};
