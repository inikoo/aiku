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
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchTextWebBlock
{
    use AsAction;
    public function processTextData(WebBlockType $webBlockType, $auroraBlock)
    {
        $block = $webBlockType->toArray();
        data_set($block, "data.fieldValue.value", $auroraBlock["text_blocks"][0]["text"]);
        return $block;
    }
}
