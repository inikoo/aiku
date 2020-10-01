<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 17:21:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands\Traits;

use Illuminate\Support\Facades\DB;

trait LegacyDataMigration
{

    public function set_legacy_connection($database_name) {

        $database_settings = data_get(config('database.connections'), 'mysql');
        data_set($database_settings, 'database', $database_name);

        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');
        DB::purge('legacy');

    }


    public function fill_data($fields, $legacy_data) {

        $data = [];
        foreach ($fields as $key => $legacy_key) {


            if (!empty($legacy_data->{$legacy_key})) {

                $key_path = preg_split('/\./', $key);
                if (count($key_path) == 1) {
                    $data[$key] = $legacy_data->{$legacy_key};
                } elseif (count($key_path) == 2) {
                    $data[$key_path[0]][$key_path[1]] = $legacy_data->{$legacy_key};
                } elseif (count($key_path) == 3) {
                    $data[$key_path[0]][$key_path[1]][$key_path[2]] = $legacy_data->{$legacy_key};
                }


            }
        }

        return $data;
    }
}
