<?php
/*
* author Arya Permana - Kirin
* created on 09-10-2024-11h-39m
* github: https://github.com/KirinZero0
* copyright 2024
*/

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchCTAWebBlock
{
    use AsAction;
    public function processCTAData(WebBlockType $webBlockType, $auroraBlock): array
    {
        data_set($layout, "data.fieldValue", Arr::get($webBlockType, "data.fieldValue"));
        data_set($layout, "data.fieldValue.title", Arr::get($auroraBlock, "title"));
        data_set($layout, "data.fieldValue.text", Arr::get($auroraBlock, "text"));
        data_set($layout, "data.fieldValue.button.text", Arr::get($auroraBlock, "button_label"));
        data_set($layout, "data.fieldValue.button.container.properties.background.color", Arr::get($auroraBlock, "bg_color"));
        data_set($layout, "data.fieldValue.button.container.properties.text.color", Arr::get($auroraBlock, "text_color"));
        data_set($layout, "data.fieldValue.button.link", Arr::get($auroraBlock, "link"));
        data_set(
            $layout,
            "data.fieldValue.button.container.properties.background.image.original",
            ["aurora_source" =>  Arr::get($auroraBlock, "bg_image")]
        );
        return $layout;
    }
}
