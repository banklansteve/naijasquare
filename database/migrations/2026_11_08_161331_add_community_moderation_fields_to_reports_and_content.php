<?php

use App\Models\Post;
use App\Models\PostReply;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('community_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
            $table->string('resolution_key', 20)->nullable()->after('reviewed_at');

            $table->index(['community_id', 'status', 'created_at']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('resolved_at');
            $table->foreignId('hidden_by_user_id')
                ->nullable()
                ->after('hidden_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('post_replies', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('body');
            $table->foreignId('hidden_by_user_id')
                ->nullable()
                ->after('hidden_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        foreach (DB::table('reports')->orderBy('id')->get(['id', 'reportable_type', 'reportable_id']) as $report) {
            $communityId = null;

            if ($report->reportable_type === Post::class) {
                $communityId = DB::table('posts')->where('id', $report->reportable_id)->value('community_id');
            } elseif ($report->reportable_type === PostReply::class) {
                $postId = DB::table('post_replies')->where('id', $report->reportable_id)->value('post_id');
                if ($postId !== null) {
                    $communityId = DB::table('posts')->where('id', $postId)->value('community_id');
                }
            }

            if ($communityId !== null) {
                DB::table('reports')->where('id', $report->id)->update([
                    'community_id' => $communityId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('post_replies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hidden_by_user_id');
            $table->dropColumn('hidden_at');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hidden_by_user_id');
            $table->dropColumn('hidden_at');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'status', 'created_at']);
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropConstrainedForeignId('community_id');
            $table->dropColumn(['reviewed_at', 'resolution_key']);
        });
    }
};
