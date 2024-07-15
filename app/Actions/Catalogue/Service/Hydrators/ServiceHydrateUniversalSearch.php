<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service\Hydrators;

use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use Lorisleiva\Actions\Concerns\AsAction;

class ServiceHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Service $service): void
    {
        $service->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $service->group_id,
                'organisation_id'   => $service->organisation_id,
                'organisation_slug' => $service->organisation->slug,
                'shop_id'           => $service->shop_id,
                'shop_slug'         => $service->shop->slug,
                'section'           => 'shops',
                'title'             => $service->name,
            ]
        );
    }

}
