<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:19:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Helpers\Address;


function process_instance_address_legacy($object, $object_key, $type, $legacy_data) {


    $_address = get_legacy_instance_address_scaffolding($object, $type, $legacy_data);


    if (!$_address->country_code and !$_address->administrative_area and !$_address->dependent_locality and !$_address->locality and !$_address->postal_code and !$_address->sorting_code and !$_address->address_line_2 and !$_address->address_line_1) {
        return null;
    }


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
        $address = (new Address)->find($addressToDelete);
        if ($address) {
            $address->deleteIfOrphan();
        }

    }

    return $customer;

}


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


function process_legacy_immutable_address($object, $type, $legacy_data) {


    $_address = get_legacy_instance_address_scaffolding($object, $type, $legacy_data);

    return (new Address)->firstOrCreate(
        [
            'checksum'   => $_address->getChecksum(),
            'owner_type' => null,
            'owner_id'   => null,

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


function get_legacy_instance_address_scaffolding($object, $type, $legacy_data) {

    if ($object == 'CustomerClient') {
        $legacy_object = 'Customer Client';
    } else {
        $legacy_object = $object;
    }


    if ($type != '') {
        $type = ' '.$type;
    }


    $_address                      = new Address();
    $_address->address_line_1      = $legacy_data->{$legacy_object.$type.' Address Line 1'};
    $_address->address_line_2      = $legacy_data->{$legacy_object.$type.' Address Line 2'};
    $_address->sorting_code        = $legacy_data->{$legacy_object.$type.' Address Sorting Code'};
    $_address->postal_code         = $legacy_data->{$legacy_object.$type.' Address Postal Code'};
    $_address->locality            = $legacy_data->{$legacy_object.$type.' Address Locality'};
    $_address->dependent_locality  = $legacy_data->{$legacy_object.$type.' Address Dependent Locality'};
    $_address->administrative_area = $legacy_data->{$legacy_object.$type.' Address Administrative Area'};
    $_address->country_code        = $legacy_data->{$legacy_object.$type.' Address Country 2 Alpha Code'};

    return $_address;


}
