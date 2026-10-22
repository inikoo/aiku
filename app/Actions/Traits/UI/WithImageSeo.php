<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 28-05-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Traits\UI;

use App\Actions\Helpers\Media\SaveModelImage;
use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterProductCategory;
use App\Models\Web\Webpage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

trait WithImageSeo
{
    public function processSeoImage(array $modelData, Webpage|MasterProductCategory|MasterAsset $model): Webpage|MasterProductCategory|MasterAsset
    {
        if (Arr::has($modelData, 'image')) {
            /** @var UploadedFile $image */
            $image = Arr::pull($modelData, 'image');
            $imageData = [
                'path'         => $image->getPathName(),
                'originalName' => $image->getClientOriginalName(),
                'extension'    => $image->getClientOriginalExtension(),
            ];

            if ($model instanceof Webpage) {
                return SaveModelImage::run(
                    model: $model,
                    imageData: $imageData,
                    scope: 'seo',
                    foreignKeyMedia: 'seo_image_id'
                );
            }

            /**
             * SaveModelImage treats the main image as the one being replaced and syncs the images
             * pivot, which would delete and detach the product images of a master. The share image
             * is only referenced by seo_image_id, so it stays out of the product image list.
             */
            $media = StoreMediaFromFile::run($model, $imageData, 'image');
            $model->updateQuietly(['seo_image_id' => $media->id]);
            $model->refresh();
        }

        return $model;
    }
}
