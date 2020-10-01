<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class LegacyDataMigrationStore extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:stores {--tenant=*}';
    protected $description = 'Migrate legacy stores';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $_table = '`Store Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('The tenant is '.$tenant->subdomain."\n");


            $this->set_legacy_connection($tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {


                $legacy_data->{'Store Can Collect'} = $legacy_data->{'Store Can Collect'} == 'Yes';


                $store_data = $this->fill_data(
                    [
                        'url'      => 'Store URL',
                        'email'    => 'Store Email',
                        'currency' => 'Store Currency Code'
                    ], $legacy_data
                );

                $store_settings = $this->fill_data(
                    [
                        'can_collect' => 'Store Can Collect',

                    ], $legacy_data
                );

                $legacy_status_to_state = [
                    'InProcess'   => 'creating',
                    'Normal'      => 'live',
                    'ClosingDown' => 'closed',
                    'Closed'      => 'closed'
                ];

                $state = $legacy_status_to_state[$legacy_data->{'Store Status'}];


                 (new Store)->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'legacy_id' => $legacy_data->{'Store Key'},

                    ], [

                        'slug'       => Str::kebab(strtolower($legacy_data->{'Store Code'})),
                        'name'       => $legacy_data->{'Store Name'},
                        'state'      => $state,
                        'data'       => $store_data,
                        'settings'   => $store_settings,
                        'created_at' => $legacy_data->{'Store Valid From'},
                        'deleted_at' => ($state == 'closed' ? $legacy_data->{'Store Valid To'} : null)
                    ]
                );


            }

        }


        return 0;


    }


}
