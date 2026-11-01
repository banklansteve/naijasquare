<?php

namespace App\Http\Controllers;

use App\Community\ReplyToPost;
use App\Http\Requests\StorePostReplyRequest;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

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
}
