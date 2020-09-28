<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Models\HR\Employee;
use App\Models\System\Guest;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class LegacyDataMigrationEmployee extends Command {

    use TenantAware;

    protected $signature = 'relocate:employees {--tenant=*}';
    protected $description = 'Migrate legacy employees';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $_table = '`Staff Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('The tenant is '.$tenant->subdomain."\n");
            $this->set_legacy_connection($tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {

                $employee_data = $this->fill_data(
                    [
                        'personal_identification' => 'Staff Official ID',
                        'next_of_kind.name'       => 'Staff Next of Kind',
                        'date_of_birth'           => 'Staff Birthday'
                    ], $legacy_data
                );



                if($legacy_data->{'Staff Type'}=='Employee'){
                    (new Employee)->firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'slug'      => Str::kebab($legacy_data->{'Staff Name'}),

                        ], [
                            'tenant_id' => $tenant->id,
                            'legacy_id' => $legacy_data->{'Staff Key'},
                            'name'      => $legacy_data->{'Staff Name'},
                            'status'    => ($legacy_data->{'Staff Currently Working'} == 'Yes' ? 'Working' : 'NotWorking'),
                            'data'      => $employee_data

                        ]
                    );
                }elseif($legacy_data->{'Staff Type'}=='Contractor'){



                    (new Guest)->firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'slug'      => Str::kebab($legacy_data->{'Staff Name'}),


                        ], [
                            'tenant_id' => $tenant->id,
                            'legacy_id' => $legacy_data->{'Staff Key'},
                            'status'    => $legacy_data->{'Staff Currently Working'} == 'Yes',
                            'data'      => $employee_data,
                            'name'      => $legacy_data->{'Staff Name'},
                            'description'=>'Contractor'

                        ]
                    );
                }




            }

        }


        return 0;


    }

    public function set_legacy_connection($database_name) {


        $database_settings = data_get(config('database.connections'), 'mysql');

        data_set($database_settings, 'database', $database_name);
        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');

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
