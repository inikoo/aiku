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

trait WithFetchProductsWebBlock
{
    use AsAction;
    public function processProductsData(WebBlockType $webBlockType, $auroraBlock): array|null
    {
        return null;
    }
}