<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 04:35:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use Illuminate\Support\Arr;

include_once 'images.php';
include_once 'addresses.php';
include_once 'products.php';
include_once 'charges.php';
include_once 'employees.php';
include_once 'shipping.php';
include_once 'adjusts.php';
include_once 'sales_common.php';
include_once 'basket.php';
include_once 'orders.php';
include_once 'invoices.php';
include_once 'delivery_notes.php';
include_once 'customer_clients.php';
include_once 'customers.php';
include_once 'prospects.php';


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





function elementsToLower($elements_keys, $array) {

    foreach ($elements_keys as $key) {
        Arr::set(
            $array, $key, strtolower(Arr::get($array, $key))
        );
    }

    return $array;

}
