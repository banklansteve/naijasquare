<?php

namespace App\Signup;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfilePhotos
{
    public function store(UploadedFile $file, int $userId): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw ValidationException::withMessages([
                'photo' => 'Choose a JPEG, PNG, or WebP photo.',
            ]);
        }

        ob_start();
        imagejpeg($image, null, 85);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        if (! is_string($jpeg) || $jpeg === '') {
            throw ValidationException::withMessages([
                'photo' => 'Choose a JPEG, PNG, or WebP photo.',
            ]);
        }

        $path = 'profile-photos/'.$userId.'.jpg';
        Storage::disk('local')->put($path, $jpeg);

        return $path;
    }
}
