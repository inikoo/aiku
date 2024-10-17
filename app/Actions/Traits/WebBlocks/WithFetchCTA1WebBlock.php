<?php
/*
* author Arya Permana - Kirin
* created on 09-10-2024-11h-39m
* github: https://github.com/KirinZero0
* copyright 2024
*/

namespace App\Actions\Traits\WebBlocks;

use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchCTA1WebBlock
{
    use AsAction;
    public function processCTA1Data($auroraBlock): array
    {
        data_set($layout, "data.fieldValue.value.title", $auroraBlock["title"]);
        data_set($layout, "data.fieldValue.value.link", $auroraBlock["link"]);
        data_set($layout, "data.fieldValue.value.icon", $auroraBlock["icon"]);
        data_set($layout, "data.fieldValue.value.button_label", $auroraBlock["button_label"]);
        data_set(
            $layout,
            "data.fieldValue.value.images",
            [["aurora_source" => $auroraBlock["bg_image"]]]
        );
        return $layout;
    }
}
