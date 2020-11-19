<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:21:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Stores\ProductHistoricVariation;


if (!function_exists('relocate_historic_products')) {
    function relocate_historic_products($legacy_data, $product_id, $tenant_id) {


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
                'tenant_id'  => $tenant_id
            ]
        );
    }
}
