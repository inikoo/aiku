<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:21:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Charge\Hydrators;

use App\Models\Billables\Charge;
use Lorisleiva\Actions\Concerns\AsAction;

class ChargeHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Charge $charge): void
    {
        $charge->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $charge->group_id,
                'organisation_id'   => $charge->organisation_id,
                'organisation_slug' => $charge->organisation->slug,
                'shop_id'           => $charge->shop_id,
                'shop_slug'         => $charge->shop->slug,
                'sections'          => ['catalogue'],
                'haystack_tier_1'   => $charge->name,
            ]
        );
    }

}
