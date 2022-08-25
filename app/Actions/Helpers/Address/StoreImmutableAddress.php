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

class StoreImmutableAddress
{
    use AsAction;

    public function handle(Address $address): Address
    {

        if ($foundAddress = Address::where('checksum', $address->getChecksum())->where('immutable', true)->first()) {
            return $foundAddress;
        }
        $address->immutable = true;
        $address->save();

      //  exit;

        return $address;
    }
}
