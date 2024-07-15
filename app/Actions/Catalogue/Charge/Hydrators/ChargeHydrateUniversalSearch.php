<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Catalogue\Charge\Hydrators;

use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Product;
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
                'section'           => 'shops',
                'title'             => $charge->name,
            ]
        );
    }

}
