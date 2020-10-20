<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 04:35:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Helpers\Address;
use App\Models\Stores\ProductHistoricVariation;
use Illuminate\Support\Arr;

if (!function_exists('legacy_process_addresses')) {
    function legacy_process_addresses($customer, $billing_address, $delivery_address) {
        $oldAddressIds = $customer->addresses->pluck('id')->all();

        if ($billing_address->id == $delivery_address->id) {
            $customer->addresses()->sync([$billing_address->id => ['scope' => 'billing_delivery']]);
        } else {
            $customer->addresses()->sync(
                [
                    $billing_address->id  => ['scope' => 'billing'],
                    $delivery_address->id => ['scope' => 'delivery']
                ]
            );

        }


        $customer->billing_address_id  = $billing_address->id;
        $customer->country_id          = $billing_address->country_id;
        $customer->delivery_address_id = $delivery_address->id;
        $customer->save();

        $customer = $customer->fresh();

        $addressIds = $customer->addresses->pluck('id')->all();


        foreach (array_diff($oldAddressIds, $addressIds) as $addressToDelete) {
            if ($address = (new Address)->find($addressToDelete)) {
                $address->deleteIfOrphan();
            }

        }

        return $customer;

    }
}
if (!function_exists('legacy_get_address')) {

    function legacy_get_address($object, $object_key, $address_data) {


        $_address = new Address();
        $_address->fill($address_data);

        return (new Address)->firstOrCreate(
            [
                'checksum'   => $_address->getChecksum(),
                'owner_type' => $object,
                'owner_id'   => $object_key,

            ], [
                'address_line_1'      => $_address->address_line_1,
                'address_line_2'      => $_address->address_line_2,
                'sorting_code'        => $_address->sorting_code,
                'postal_code'         => $_address->postal_code,
                'locality'            => $_address->locality,
                'dependent_locality'  => $_address->dependent_locality,
                'administrative_area' => $_address->administrative_area,
                'country_code'        => $_address->country_code,

            ]
        );

    }
}

if (!function_exists('fill_legacy_data')) {
    function fill_legacy_data($fields, $legacy_data, $modifier = false) {

        $data = [];
        foreach ($fields as $key => $legacy_key) {
            if (!empty($legacy_data->{$legacy_key})) {
                if ($modifier == 'strtolower') {
                    $value = strtolower($legacy_data->{$legacy_key});
                } elseif ($modifier == 'jsonDecode') {
                    $value = json_decode($legacy_data->{$legacy_key}, true);
                } else {
                    $value = $legacy_data->{$legacy_key};
                }
                Arr::set($data, $key, $value);
            }
        }

        return $data;
    }

}


if (!function_exists('relocate_historic_products')) {
    function relocate_historic_products($legacy_data, $product_id) {



        $historic_product_data = fill_legacy_data(
            [
                'code' => 'Product History Code',
                'name' => 'Product History Name'
            ], $legacy_data
        );


        $units = $legacy_data->{'Product History Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        if ($legacy_data->{'Product History Valid From'} == '0000-00-00 00:00:00') {
            $date = null;
        } else {
            $date = $legacy_data->{'Product History Valid From'};
        }

        return (new ProductHistoricVariation)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Product Key'},
            ], [

                'product_id' => $product_id,
                'unit_price' => $legacy_data->{'Product History Price'} / $units,
                'units'      => $units,
                'data'       => $historic_product_data,
                'date'       => $date,
            ]
        );
    }
}


