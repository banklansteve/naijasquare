<?php

namespace App\Community;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CommunityCovers
{
    public function store(UploadedFile $file, int $communityId): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw ValidationException::withMessages([
                'cover' => 'Choose a JPEG, PNG, or WebP image.',
            ]);
        }

        ob_start();
        imagejpeg($image, null, 85);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        if (! is_string($jpeg) || $jpeg === '') {
            throw ValidationException::withMessages([
                'cover' => 'Choose a JPEG, PNG, or WebP image.',
            ]);
        }

        $path = 'community-covers/'.$communityId.'.jpg';
        Storage::disk('local')->put($path, $jpeg);

        return $path;
    }
}
