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

trait WithFetchFamilyWebBlock
{
    use AsAction;
    public function processFamilyData(WebBlockType $webBlockType, $auroraBlock): array|null
    {

        if (!isset($auroraBlock["type"])) {
            return null;
        }

        $layout = Arr::only(
            $webBlockType->toArray(),
            [
                'code','data','name'
            ]
        );

        $items = [];
        foreach ($auroraBlock['items'] as $index => $item) {
            $type = $item["type"];
            if ($type == "product") {
                continue;
            } elseif ($type == "video") {
                $items[] = ["position" => $index,"type" => $type, "video_id" => $item["video_id"]];
            } elseif ($type == 'image') {
                $items[] =  ['position' => $index, "type" => $type, "aurora_source" => $item["image_src"]];
            } else {
                dd("dd inside processFammilyData => ", $type);
            }
        }

        data_set($layout, "data.fieldValue.value.items", $items);
        return $layout;
    }
}
