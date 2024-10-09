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

trait WithFetchProductWebBlock
{
    use AsAction;
    public function processProductData(WebBlockType $webBlockType, $auroraBlock): array
    {
        $layout = Arr::only(
            $webBlockType->toArray(),
            [
                'code','data','name'
            ]
        );
        data_set($layout, "data.fieldValue.value.text", $auroraBlock["text"]);
        data_set(
            $layout,
            "data.fieldValue.value.image.aurora_source",
            $auroraBlock["image"]["src"]
        );

        $otherImages = [];
        foreach ($auroraBlock["other_images"] as $image) {
            if (!isset($image["src"])) {
                continue;
            }
            $otherImages[] = [
                "aurora_source" => $image["src"],
            ];
        }
        $imgValue["value"] = $otherImages;
        data_set($layout, "data.fieldValue.value.other_images", $imgValue["value"]);
        return $layout;
    }
}