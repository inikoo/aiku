<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 17 Oct 2023 15:23:45 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Actions\Media\Media\UpdateIsAnimatedMedia;
use App\Models\CRM\WebUser;
use App\Models\Mail\EmailTemplate;
use App\Models\Media\Media;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Models\Web\Website;

trait WithUpdateModelImage
{
    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function updateModelImage(
        Website|Organisation|User|EmailTemplate|WebUser $model,
        string $collection,
        string $field,
        string $imagePath,
        string $originalFilename,
        string $extension = null,
        array $properties = []
    ): Website|Organisation|User|EmailTemplate|WebUser {
        $checksum = md5_file($imagePath);


        if ($model->getMedia($collection, ['checksum' => $checksum])->count() == 0) {
            $model->update([$field => null]);

            /** @var Media $media */
            $media = $model->addMedia($imagePath)
                ->preservingOriginal()
                ->withProperties(
                    array_merge(
                        [
                            'checksum' => $checksum,
                        ],
                        $properties
                    )
                )
                ->usingName($originalFilename)
                ->usingFileName($checksum.".".$extension ?? pathinfo($imagePath, PATHINFO_EXTENSION))
                ->toMediaCollection($collection);
            $media->refresh();
            UpdateIsAnimatedMedia::run($media, $imagePath);

            $model->update([$field => $media->id]);
        }


        return $model;
    }
}
