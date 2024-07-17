<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping\Hydrators;

use App\Models\Catalogue\Shipping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShippingHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Shipping $shipping): void
    {
        $shipping->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $shipping->group_id,
                'organisation_id'   => $shipping->organisation_id,
                'organisation_slug' => $shipping->organisation->slug,
                'shop_id'           => $shipping->shop_id,
                'shop_slug'         => $shipping->shop->slug,
                'section'           => 'shops',
                'title'             => $shipping->name,
            ]
        );
    }

}
