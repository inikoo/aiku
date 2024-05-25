<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 May 2024 13:58:23 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateFixedUsage;
use App\Models\Helpers\Address;

trait WithFixedAddressActions
{
    protected function findFixedAddress(Address $address, string $fixedScope): ?Address
    {
        return Address::where('checksum', $address->getChecksum())
            ->where('is_fixed', true)
            ->where('fixed_scope', $fixedScope)
            ->first();
    }


    protected function createFixedAddress($model, Address $addressTemplate, string $fixedScope, $scope, $addressField)
    {
        if (!$address = $this->findFixedAddress($addressTemplate, $fixedScope)) {
            $modelData = $addressTemplate->toArray();
            data_set($modelData, 'is_fixed', true);
            data_set($modelData, 'fixed_scope', $fixedScope);

            $address = Address::create($modelData);
        }

        $model->fixedAddresses()->attach(
            $address->id,
            [
                'scope' => $scope
            ]
        );

        AddressHydrateFixedUsage::dispatch($address);
        $model->updateQuietly([$addressField => $address->id]);

        return $model;
    }

}
