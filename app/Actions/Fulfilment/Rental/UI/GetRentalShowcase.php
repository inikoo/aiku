<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental\UI;

use App\Models\Fulfilment\Rental;
use Lorisleiva\Actions\Concerns\AsObject;

class GetRentalShowcase
{
    use AsObject;

    public function handle(Rental $rental): array
    {
        return [
            []
        ];
    }
}
