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
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchOverviewWebBlock
{
    use AsAction;
    public function processOverviewData(WebBlockType $webBlockType, Webpage $webpage, $auroraBlock): array
    {
        data_set($layout, "data.fieldValue", Arr::get($webBlockType, 'data.fieldValue'));
        $textsArray = [];
        foreach ($auroraBlock["texts"] as $text) {
            if (!isset($text["text"])) {
                continue;
            }
            $this->replaceAnchor($webpage, $text["text"], $layout); // should use WithFetchText
            $textsArray[] = $text["text"];
        }

        data_set($layout, "data.fieldValue.text.values", $textsArray);

        $imagesArray = [];
        foreach ($auroraBlock["images"] as $image) {
            if (!isset($image["src"])) {
                continue;
            }
            $imagesArray[] = [
                "aurora_source" => $image["src"],
            ];
        }

        data_set($layout, "data.fieldValue.images.source", $imagesArray);
        return $layout;
    }
}
