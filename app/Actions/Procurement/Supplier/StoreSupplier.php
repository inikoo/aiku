<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:53:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Helpers\Address\StoreAddress;
use App\Models\Central\Tenant;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreSupplier
{
    use AsAction;

    public function handle(Tenant|Supplier $parent, array $modelData, array $addressData = []): Supplier
    {
        /** @var Supplier $supplier */
        $supplier = $parent->suppliers()->create($modelData);

        $supplier->stats()->create();


        if (count($addressData) > 0) {
            $addresses               = [];
            $address                 = StoreAddress::run($addressData);
            $addresses[$address->id] = ['scope' => 'default'];
            $supplier->addresses()->sync($addresses);
            $supplier->address_id = $address->id;
            $supplier->location   = $supplier->getLocation();
            $supplier->save();
        }

        return $supplier;
    }


}
