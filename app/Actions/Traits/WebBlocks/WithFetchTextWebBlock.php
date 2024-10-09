<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Oct 2024 12:36:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\WebBlocks;

use App\Models\Web\WebBlockType;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithFetchTextWebBlock
{
    use AsAction;
    public function processTextData(WebBlockType $webBlockType, $auroraBlock): array
    {
        $layout = Arr::only(
            $webBlockType->toArray(),
            [
                'code','data','name'
            ]
        );
        data_set($layout, "data.fieldValue.value", $auroraBlock["text_blocks"][0]["text"]);
        return $layout;
    }
}
