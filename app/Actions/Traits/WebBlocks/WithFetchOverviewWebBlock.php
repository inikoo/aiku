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

trait WithFetchOverviewWebBlock
{
    use AsAction;
    public function processOverviewData(WebBlockType $webBlockType, $auroraBlock): array
    {
        $layout = Arr::only(
            $webBlockType->toArray(),
            [
                'code','data','name'
            ]
        );
        $textsArray = [];
        foreach ($auroraBlock["texts"] as $text) {
            if (!isset($text["text"])) {
                continue;
            }
            $textsArray[] = [
                "text" => $text["text"],
            ];
        }
        $textValue["value"] = $textsArray;
        data_set($layout, "data.fieldValue.value.texts", $textValue["value"]);

        $imagesArray = [];
        foreach ($auroraBlock["images"] as $image) {
            if (!isset($image["src"])) {
                continue;
            }
            $imagesArray[] = [
                "aurora_source" => $image["src"],
            ];
        }
        $imgValue["value"] = $imagesArray;
        data_set($layout, "data.fieldValue.value.images", $imgValue["value"]);
        return $layout;
    }
}
