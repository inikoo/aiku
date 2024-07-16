<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental\Hydrators;

use App\Models\Fulfilment\Rental;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Rental $rental): void
    {
        $rental->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $rental->group_id,
                'organisation_id'   => $rental->organisation_id,
                'organisation_slug' => $rental->organisation->slug,
                'shop_id'           => $rental->shop_id,
                'shop_slug'         => $rental->shop->slug,
                'section'           => 'shops',
                'title'             => $rental->name,
            ]
        );
    }

}
