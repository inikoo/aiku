<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 17:21:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands\Traits;

use App\Models\Helpers\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait LegacyDataMigration {

    public function set_legacy_connection($database_name) {

        $database_settings = data_get(config('database.connections'), 'mysql');
        data_set($database_settings, 'database', $database_name);

        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');
        DB::purge('legacy');

    }


    public function fill_data($fields, $legacy_data, $modifier = false) {

        $data = [];
        foreach ($fields as $key => $legacy_key) {
            if (!empty($legacy_data->{$legacy_key})) {
                if ($modifier == 'strtolower') {
                    $value = strtolower($legacy_data->{$legacy_key});
                }elseif ($modifier == 'jsonDecode') {
                    $value = jsondecode($legacy_data->{$legacy_key},true);
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

    function get_instance_address_scaffolding($object,$type,$legacy_data){

        $legacy_object=$object;

        $_address = new Address();
        $_address->address_line_1 = $legacy_data->{$legacy_object.' '.$type.' Address Line 1'};
        $_address->address_line_2 = $legacy_data->{$legacy_object.' '.$type.' Address Line 2'};
        $_address->sorting_code        = $legacy_data->{$legacy_object.' '.$type.' Address Sorting Code'};
        $_address->postal_code         = $legacy_data->{$legacy_object.' '.$type.' Address Postal Code'};
        $_address->locality            = $legacy_data->{$legacy_object.' '.$type.' Address Locality'};
        $_address->dependent_locality  = $legacy_data->{$legacy_object.' '.$type.' Address Dependent Locality'};
        $_address->administrative_area = $legacy_data->{$legacy_object.' '.$type.' Address Administrative Area'};
        $_address->country_code = $legacy_data->{$legacy_object.' '.$type.' Address Country 2 Alpha Code'};

        return $_address;



    }


    function process_instance_address($object,$object_key,$type,$legacy_data){



        $_address=$this->get_instance_address_scaffolding($object,$type,$legacy_data);

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
                'country_code' => $_address->country_code,

            ]
        );


    }

}
