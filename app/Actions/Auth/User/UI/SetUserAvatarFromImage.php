<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 15:17:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\Auth\User\UpdateUser;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Auth\User;
use App\Models\Media\Media;
use Lorisleiva\Actions\Concerns\AsAction;

class SetUserAvatarFromImage
{
    use AsAction;
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(User $user, string $imagePath, string $originalFilename, string $extension=null): User
    {
        $checksum = md5_file($imagePath);

        if ($user->getMedia('profile', ['checksum' => $checksum])->count() == 0) {

            $user->update(['avatar_id' => null]);

            $filename=dechex(crc32($checksum)).'.';
            $filename.=empty($extension) ? pathinfo($imagePath, PATHINFO_EXTENSION) : $extension;

            /** @var Media $media */
            $media=$user->addMedia($imagePath)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($originalFilename)
                ->usingFileName($filename)
                ->toMediaCollection('profile', 'group');


            $avatarID = $media->id;

            UpdateUser::run($user, ['avatar_id' => $avatarID]);

        }


        return $user;
    }
}
