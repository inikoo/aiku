<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\Shop;

class UpdateShop
{
    use WithActionUpdate;

    public function handle(Shop $shop, array $modelData): Shop
    {
        return $this->update($shop, $modelData, ['data', 'settings']);
    }
}
