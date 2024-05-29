<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 May 2024 23:16:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Models\Helpers\Address;

trait WithModelAddressActions
{
    protected function updateLocation($model, $address)
    {
        $model->updateQuietly(
            [
                'location' => $address->getLocation()
            ]
        );

        return $model;
    }

    protected function updateAddressField($model, $address, $field = 'address_id')
    {
        $model->updateQuietly(
            [
                $field => $address->id
            ]
        );

        return $model;
    }


    protected function updateModelAddress($model, $addressData, $updateLocation = true, $updateAddressField = 'address_id')
    {
        if (!$addressData) {
            return $model;
        }
        if ($model->address) {
            $address = $model->address;
            $address->update($addressData);

            if ($updateLocation) {
                $this->updateLocation($model, $address);
            }


            return $model;
        } else {
            return $this->addAddressToModel(model: $model, addressData: $addressData, updateLocation: $updateLocation, updateAddressField: $updateAddressField);
        }
    }

    protected function addAddressToModel($model, $addressData, $scope = 'default', $updateLocation = true, $updateAddressField = 'address_id')
    {
        if (!$addressData) {
            return $model;
        }

        $address = Address::create($addressData);
        $model->addresses()->attach(
            $address->id,
            [
                'scope' => $scope
            ]
        );

        AddressHydrateUsage::dispatch($address);

        if ($updateLocation) {
            $this->updateLocation($model, $address);
        }
        if ($updateAddressField) {
            $this->updateAddressField($model, $address, $updateAddressField);
        }

        return $model;
    }

    protected function addDirectAddress($model, $addressData)
    {
        data_set($addressData, 'is_fixed', true);
        data_set($addressData, 'usage', 1);

        $address = Address::create($addressData);

        $model->updateQuietly(
            [
                'address_id' => $address->id,
                'location'   => $address->getLocation()
            ]
        );

        return $model;
    }

    protected function addLinkedAddress($model, $scope = 'default', $updateLocation = true, $updateAddressField = 'address_id')
    {
        $addressLink  = explode(':', $model->settings['address_link']);
        $address      = null;
        $addressModel = null;
        if ($addressLink[0] == 'Organisation') {
            $addressModel = $model->organisation;
        }


        if ($addressModel) {
            $address = $addressModel->addresses()->where('scope', $addressLink[1])->first();
        }


        if ($address) {
            $model->addresses()->attach(
                $address->id,
                [
                    'scope' => $scope
                ]
            );
            AddressHydrateUsage::dispatch($address);
            if ($updateLocation) {
                $this->updateLocation($model, $address);
            }
            if ($updateAddressField) {
                $this->updateAddressField($model, $address, $updateAddressField);
            }
        }


        return $model;
    }


}