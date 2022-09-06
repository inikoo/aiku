<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:34:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\WithUpdate;
use App\Models\Auth\User;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property User $user
 */
class SetAvatarFromImage
{
    use AsAction;
    use WithUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(User $user, string $image_path, string $filename): ActionResult
    {
        $res = new ActionResult();

        $checksum = md5_file($image_path);

        if ($user->getMedia('profile', ['checksum' => $checksum])->count() == 0) {
            $user->addMedia($image_path)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($filename)
                ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                ->toMediaCollection('profile');


            $user->update(
                [
                    'data->profile_url' => $user->refresh()->getFirstMediaUrl('profile'),
                    'data->profile_source' => 'Media'
                ]
            );
            $res->changes = array_merge($res->changes, $user->getChanges());
        }

        $res->model    = $user;
        $res->model_id = $user->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';

        return $res;
    }


}
