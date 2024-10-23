<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
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
            $this->setProperties($property, $text);
            $textsArray[] = [
                'properties' => $property,
                'text' => $text["text"]
            ];
        }

        data_set($layout, "data.fieldValue.texts.values", $textsArray);

        $imagesArray = [];
        foreach ($auroraBlock["images"] as $image) {
            if (!isset($image["src"])) {
                continue;
            }
            $this->setProperties($property, $image);
            $imagesArray[] = [
                "properties" => $property,
                "aurora_source" => $image["src"],
            ];
        }

        data_set($layout, "data.fieldValue.images", $imagesArray);
        return $layout;
    }

    private function setProperties(&$properties, $propertiesAurora)
    {
        data_set($properties, 'position.top', Arr::get($propertiesAurora, 'top'));
        data_set($properties, 'position.left', Arr::get($propertiesAurora, 'left'));
        data_set($properties, 'position.bottom', Arr::get($propertiesAurora, 'bottom'));
        data_set($properties, 'position.right', Arr::get($propertiesAurora, 'right'));
        data_set($properties, 'width', Arr::get($propertiesAurora, 'width'));
        data_set($properties, 'height', Arr::get($propertiesAurora, 'height'));
    }
}
