<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Jul 2023 01:26:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser\UI;

use App\Actions\Auth\GroupUser\UpdateGroupUser;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Auth\GroupUser;
use App\Models\Media\GroupMedia;
use Lorisleiva\Actions\Concerns\AsAction;

class SetGroupUserAvatarFromImage
{
    use AsAction;
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(GroupUser $groupUser, string $imagePath, string $originalFilename, string $extension=null): GroupUser
    {
        $checksum = md5_file($imagePath);

        if ($groupUser->getMedia('profile', ['checksum' => $checksum])->count() == 0) {

            $groupUser->update(['avatar_id' => null]);

            $filename=$checksum.'.';
            $filename.=empty($extension) ? pathinfo($imagePath, PATHINFO_EXTENSION) : $extension;

            /** @var GroupMedia $groupMedia */
            $groupMedia=$groupUser->addMedia($imagePath)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($originalFilename)
                ->usingFileName($filename)
                ->toMediaCollection('profile', 'group');


            $avatarID = $groupMedia->id;

            UpdateGroupUser::run($groupUser, ['avatar_id' => $avatarID]);

        }


        return $groupUser;
    }
}
