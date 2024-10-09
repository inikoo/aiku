<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
 */

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchGalleryWebBlock
{
    use AsAction;
    public function processGalleryData(WebBlockType $webBlockType, $auroraBlock): array|null
    {
        if (!isset($auroraBlock["images"])) {
            return null;
        }
        $layout = Arr::only(
            $webBlockType->toArray(),
            [
                'code','data','name'
            ]
        );

        $imagesArray = [];
        foreach ($auroraBlock["images"] as $image) {
            if (!isset($image["src"])) {
                continue;
            }
            $imagesArray[] = [
                "aurora_source" => $image["src"],
            ];
        }
        // $fieldValue["value"] = $imagesArray;
        data_set($layout, "data.fieldValue.value.images", $imagesArray);
        // dd($layout);
        return $layout;
    }
}
