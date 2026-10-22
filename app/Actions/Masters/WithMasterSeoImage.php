<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters;

use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterProductCategory;
use Illuminate\Http\UploadedFile;

trait WithMasterSeoImage
{
    /**
     * SaveModelImage treats the main image as the one being replaced and syncs the images pivot,
     * which would delete and detach the product images of a master. The share image is only
     * referenced by seo_image_id, so it stays out of the product image list.
     */
    public function processMasterSeoImage(UploadedFile $image, MasterProductCategory|MasterAsset $model): MasterProductCategory|MasterAsset
    {
        $media = StoreMediaFromFile::run($model, [
            'path'         => $image->getPathName(),
            'originalName' => $image->getClientOriginalName(),
            'extension'    => $image->getClientOriginalExtension(),
            'checksum'     => md5_file($image->getPathName()),
        ], 'image');

        $model->updateQuietly(['seo_image_id' => $media->id]);

        return $model->refresh();
    }
}
