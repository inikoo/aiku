<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\Helpers\Address\StoreAddress;
use App\Actions\StoreModelAction;
use App\Models\Marketing\Shop;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShop extends StoreModelAction
{
    use AsAction;

    public function handle(Organisation $organisation,array $modelData, array $addressData = []): ActionResult
    {
        /** @var Shop $shop */
        $shop = $organisation->shops()->create($modelData);
        $shop->stats()->create();


        if (count($addressData) > 0) {
            $addresses = [];

            $address = StoreAddress::run($addressData);

            $addresses[$address->id] = ['scope' => 'collection'];

            $shop->addresses()->sync($addresses);
            $shop->address_id = $address->id;
            $shop->location   = $shop->getLocation();
            $shop->save();
        }

        return $this->finalise($shop);
    }


}
