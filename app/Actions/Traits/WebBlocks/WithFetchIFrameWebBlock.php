<?php

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchIFrameWebBlock
{
    use AsAction;
    public function processIFrameData(WebBlockType $webBlockType, $auroraBlock)
    {
        $block = $webBlockType->toArray();
        data_set($block, "data.fieldValue.link", $auroraBlock["src"]);
        return $block;
    }
}
