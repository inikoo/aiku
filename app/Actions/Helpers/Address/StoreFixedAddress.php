<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 07 Dec 2021 01:28:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Helpers\Address;

use App\Models\Helpers\Address;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFixedAddress
{
    use AsAction;

    public function handle(Address $address, string $fixedScope): Address
    {
        if ($foundAddress = Address::where('checksum', $address->getChecksum())
            ->where('is_fixed', true)
            ->where('fixed_scope', $fixedScope)
            ->first()) {
            return $foundAddress;
        }



        return StoreAddress::run(array_merge($address->toArray(), ['historic' => true]));
    }
}
