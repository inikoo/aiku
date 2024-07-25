<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 14:03:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental\Search;

use App\Models\Fulfilment\Rental;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Rental $rental): void
    {

        if ($rental->trashed()) {

            if($rental->universalSearch) {
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
