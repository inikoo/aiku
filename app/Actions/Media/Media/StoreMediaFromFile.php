<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Media\Media;

use App\Models\Media\Media;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMediaFromFile
{
    use AsAction;

    public function handle($model, $imageData, $collection): Media
    {
        $checksum = md5_file($imageData['path']);
        $oldImage = $model->image;

        if ($oldImage && $oldImage->checksum == $checksum) {
            return $model->image;
        }

        $extension = Arr::get($imageData, 'extension');
        if (!$extension) {
            $extension = pathinfo($imageData['path'], PATHINFO_EXTENSION);
        }

        $media = $model->addMedia($imageData['path'])
            ->preservingOriginal()
            ->withProperties(
                array_merge(
                    [
                        'checksum' => $checksum,
                        'group_id' => group()->id
                    ],
                )
            )
            ->usingName($imageData['originalName'])
            ->usingFileName($checksum.'.'.$extension)
            ->toMediaCollection($collection);

        UpdateIsAnimatedMedia::run($media, $imageData['path']);

        return $media;
    }
}
