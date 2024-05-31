<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Media;

use App\Models\Studio\Media;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMediaFromFile
{
    use AsAction;

    public function handle($model, $imageData, $collection, $type = 'image'): Media
    {
        $extension = Arr::get($imageData, 'extension');
        if (!$extension) {
            $extension = pathinfo($imageData['path'], PATHINFO_EXTENSION);
        }

        $media = $model->addMedia($imageData['path'])
            ->preservingOriginal()
            ->withProperties(
                array_merge(
                    [
                        'checksum' => $imageData['checksum'],
                        'group_id' => group()->id,
                        'type'     => $type
                    ],
                )
            )
            ->usingName($imageData['originalName'])
            ->usingFileName($imageData['checksum'].'.'.$extension)
            ->toMediaCollection($collection);

        UpdateIsAnimatedMedia::run($media, $imageData['path']);

        return $media;
    }
}
