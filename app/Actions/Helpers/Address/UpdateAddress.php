<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 27 Sep 2021 18:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Helpers\Address;

use App\Actions\WithActionUpdate;
use App\Models\Helpers\Address;

class UpdateAddress
{
    use WithActionUpdate;

    public function handle(Address $address, array $modelData): Address
    {
        return $this->update($address, $modelData);
    }
}
