<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 29 Mar 2022 00:42:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\User;

use App\Actions\WithUpdate;
use App\Models\Organisations\User;
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
