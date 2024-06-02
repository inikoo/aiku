<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 16:47:45 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Media;

use App\Actions\Studio\Media\Hydrators\MediaHydrateMultiplicity;
use App\Actions\Studio\Media\Hydrators\MediaHydrateUsage;
use App\Models\Catalogue\Asset;
use App\Models\Studio\Media;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveModelImages
{
    use AsAction;

    public function handle(
        Asset $model,
        array $imageData,
        string $scope = 'image',
        string $mediaScope = 'images'
    ): Asset {
        $group_id        = $model->group_id;
        $organisation_id = $model->organisation_id;

        $checksum = md5_file($imageData['path']);


        $media = Media::where('collection_name', $mediaScope)->where('group_id', $group_id)->where('checksum', $checksum)->first();

        if (!$media) {


            data_set($imageData, 'checksum', $checksum);

            $media = StoreMediaFromFile::run($model, $imageData, $mediaScope);
        } elseif($model->images()->where('media_id', $media->id)->exists()) {
            return $model;
        }




        if ($media) {
            $model->images()->attach(
                [
                    $media->id => [
                        'group_id'        => $group_id,
                        'organisation_id' => $organisation_id,
                        'scope'           => $scope,
                    ]
                ]
            );
            if ($model->images()->count() == 1) {
                $model->updateQuietly(['image_id' => $media->id]);
            }

            MediaHydrateUsage::dispatch($media);
            MediaHydrateMultiplicity::dispatch($media);
        }

        return $model;
    }


}
