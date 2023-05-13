<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 00:49:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\Helpers\Address;

trait HasAddress
{
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
