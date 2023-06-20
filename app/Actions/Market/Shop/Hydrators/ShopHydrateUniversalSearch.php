<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:06:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Market\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shop $shop): void
    {
        $shop->universalSearch()->create(
            [
                'primary_term'   => $shop->name,
                'secondary_term' => $shop->code
            ]
        );
    }

}
