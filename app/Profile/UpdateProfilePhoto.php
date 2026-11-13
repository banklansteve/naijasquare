<?php

namespace App\Profile;

use App\Models\User;
use App\Signup\ProfilePhotos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfilePhoto
{
    public function __construct(private ProfilePhotos $photos) {}

    public function store(User $user, UploadedFile $photo): User
    {
        $previous = $user->photo_path;
        $path = $this->photos->store($photo, $user->id);

        $user->forceFill([
            'photo_path' => $path,
        ])->save();

        if (is_string($previous) && $previous !== '' && $previous !== $path) {
            Storage::disk('local')->delete($previous);
        }

        return $user->refresh();
    }

    public function remove(User $user): User
    {
        if (is_string($user->photo_path) && $user->photo_path !== '') {
            Storage::disk('local')->delete($user->photo_path);
        }

        $user->forceFill([
            'photo_path' => null,
        ])->save();

        return $user->refresh();
    }
}
