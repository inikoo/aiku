<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 20:58:56 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Rental\Search;

use App\Models\Billables\Rental;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Rental $rental): void
    {

        if ($rental->trashed()) {

            if ($rental->universalSearch) {
                $rental->universalSearch()->delete();
            }
            return;
        }

        $rental->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $rental->group_id,
                'organisation_id'   => $rental->organisation_id,
                'organisation_slug' => $rental->organisation->slug,
                'shop_id'           => $rental->shop_id,
                'shop_slug'         => $rental->shop->slug,
                'fulfilment_id'     => $rental->shop->fulfilment->id,
                'fulfilment_slug'   => $rental->shop->fulfilment->slug,
                'sections'          => ['catalogue'],
                'haystack_tier_1'   => $rental->name,
            ]
        );
    }

}
