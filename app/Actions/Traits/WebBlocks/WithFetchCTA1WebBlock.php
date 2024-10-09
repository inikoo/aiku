<?php
/*
* author Arya Permana - Kirin
* created on 09-10-2024-11h-39m
* github: https://github.com/KirinZero0
* copyright 2024
*/

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchCTA1WebBlock
{
    use AsAction;
    public function processCTA1Data(WebBlockType $webBlockType, $auroraBlock)
    {
        $block = $webBlockType->toArray();
        data_set($block, "data.fieldValue.value.title", $auroraBlock["title"]);
        data_set($block, "data.fieldValue.value.link", $auroraBlock["link"]);
        data_set($block, "data.fieldValue.value.icon", $auroraBlock["icon"]);
        data_set($block, "data.fieldValue.value.button_label", $auroraBlock["button_label"]);
        data_set(
            $block,
            "data.fieldValue.value.bg_image.aurora_source",
            $auroraBlock["bg_image"]
        );
        return $block;
    }
}
