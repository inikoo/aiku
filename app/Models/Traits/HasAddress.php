<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 02 Jan 2022 15:30:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Traits;

use App\Models\Helpers\Address;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddress
{

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function getLocation($scope = 'contact'): array
    {
        $location = [null, '', ''];
        if ($contactAddress = $this->getAddress($scope)) {
            $location = $contactAddress->getLocation() ?? [null, '', ''];
        }

        return $location;
    }

    public function getAddress($scope): ?Address
    {
        return $this->addresses()->where('scope', '=', $scope)->first();
    }


}


