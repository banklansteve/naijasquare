<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileAvatarController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $path = $request->user()->photo_path;

        abort_unless(is_string($path) && $path !== '', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response(
            $path,
            headers: ['Cache-Control' => 'private, max-age=3600'],
        );
    }
}
