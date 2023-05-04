<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\WithActionUpdate;
use App\Models\Auth\User;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property \App\Models\Auth\User $user
 */
class SetAvatarFromImage
{
    use AsAction;
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(User $user, string $image_path, string $filename): User
    {
        $checksum = md5_file($image_path);

        if ($user->getMedia('profile', ['checksum' => $checksum])->count() == 0) {
            $user->addMedia($image_path)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($filename)
                ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                ->toMediaCollection('profile', 'group');


            $user->update(
                [
                    'data->profile_url'    => $user->refresh()->getFirstMediaUrl('profile'),
                    'data->profile_source' => 'GroupMedia'
                ]
            );
        }


        return $user;
    }
}
