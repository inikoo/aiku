<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable\UI;

use App\Models\Catalogue\Billable;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProductService
{
    use AsObject;

    public function handle(Billable $product): array
    {
        $service = $product->service;
        return [
            $service
        ];
    }
}
