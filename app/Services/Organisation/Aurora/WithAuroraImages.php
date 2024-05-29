<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:55:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait WithAuroraImages
{
    public function getModelImagesCollection($model, $id): Collection
    {
        return DB::connection('aurora')
            ->table('Image Subject Bridge')
            ->leftJoin('Image Dimension', 'Image Subject Image Key', '=', 'Image Key')
            ->where('Image Subject Object', $model)
            ->where('Image Subject Object Key', $id)
            ->orderByRaw("FIELD(`Image Subject Is Principal`, 'Yes','No')")
            ->get();
    }

    public function fetchImage($auroraImageData): array
    {
        $image_path = sprintf(
            config('app.aurora_image_path'),
            Arr::get($this->organisation->source, 'account_code')
        );


        $image_path .= '/'
            .$auroraImageData->{'Image File Checksum'}[0].'/'
            .$auroraImageData->{'Image File Checksum'}[1].'/'
            .$auroraImageData->{'Image File Checksum'}.'.'
            .$auroraImageData->{'Image File Format'};


        if (file_exists($image_path)) {
            return [
                'image_path' => $image_path,
                'filename'   => $auroraImageData->{'Image Filename'},
                'mime'       => $auroraImageData->{'Image MIME Type'},

            ];
        } else {
            return [];
        }
    }
}
