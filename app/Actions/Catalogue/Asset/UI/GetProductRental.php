<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset\UI;

use App\Models\Catalogue\Asset;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProductRental
{
    use AsObject;

    public function handle(Asset $product): array
    {
        $rental = $product->rental;
        return [
            $rental
        ];
    }
}
