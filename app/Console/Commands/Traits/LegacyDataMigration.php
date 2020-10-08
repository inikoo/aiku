<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 17:21:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands\Traits;

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


}
