<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:42:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebBlock
{
    use AsAction;

    public function handle(WebBlockType $webBlockType, array $modelData): WebBlock
    {
        data_set($modelData, 'scope', $webBlockType->scope);
        /** @var WebBlock $webBlock */
        $webBlock = $webBlockType->webBlock()->create($modelData);
        $webBlock->stats()->create();
        return $webBlock;
    }
}
