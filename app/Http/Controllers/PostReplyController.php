<?php

namespace App\Http\Controllers;

use App\Community\MarkReplyHelped;
use App\Community\ReplyToPost;
use App\Http\Requests\StorePostReplyRequest;
use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostReplyController extends Controller
{
    public function store(
        StorePostReplyRequest $request,
        Community $community,
        Post $post,
        ReplyToPost $reply,
    ): RedirectResponse {
        abort_unless($post->community_id === $community->id, 404);

        $reply->handle($request->user(), $post, $request->validated('body'));

        return back()->with('status', 'Reply posted.');
    }

    public function markHelped(
        Request $request,
        Community $community,
        Post $post,
        PostReply $reply,
        MarkReplyHelped $markHelped,
    ): RedirectResponse {
        abort_unless($post->community_id === $community->id, 404);
        abort_unless($reply->post_id === $post->id, 404);

        $markHelped->mark($request->user(), $reply);

        return back()->with('status', 'Marked as helpful.');
    }

    public function unmarkHelped(
        Request $request,
        Community $community,
        Post $post,
        PostReply $reply,
        MarkReplyHelped $markHelped,
    ): RedirectResponse {
        abort_unless($post->community_id === $community->id, 404);
        abort_unless($reply->post_id === $post->id, 404);

        $markHelped->unmark($request->user(), $reply);

        return back()->with('status', 'Removed helpful mark.');
    }
}
