<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:07:04 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlockType;

use App\Models\Web\WebBlockType;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebBlockType
{
    use AsAction;

    public function handle(array $modelData): WebBlockType
    {
        /** @var WebBlockType $webBlockType */
        $webBlockType = WebBlockType::create($modelData);
        $webBlockType->stats()->create();

        return $webBlockType;
    }
}
