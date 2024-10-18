<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
 */

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchOverviewWebBlock
{
    use AsAction;
    public function processOverviewData(Webpage $webpage, $auroraBlock): array
    {
        $textsArray = [];
        foreach ($auroraBlock["texts"] as $text) {
            if (!isset($text["text"])) {
                continue;
            }
            $this->replaceAnchor($webpage, $text["text"]); // should use WithFetchText
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
        data_set($layout, "data.fieldValue.value.images", $imagesArray);
        return $layout;
    }
}
