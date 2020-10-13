<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;

use App\Models\ECommerce\Website;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateStores extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:stores {--tenant=*}';
    protected $description = 'Relocate legacy stores';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $this->tenant = Tenant::current();

        $legacy_stores_table   = '`Store Dimension`';
        $legacy_websites_table = '`Website Dimension`';

        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation Stores/Websites '.$this->tenant->subdomain."\n");
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_stores_table, []) as $legacy_data) {
                $this->relocate_stores($legacy_data);
            }


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_websites_table, []) as $legacy_data) {
                $this->relocate_websites($legacy_data);
            }




        }


        return 0;


    }

    function relocate_stores($legacy_data) {


        $legacy_data->{'Store Can Collect'} = $legacy_data->{'Store Can Collect'} == 'Yes';


        $website_data = $this->fill_data(
            [
                'url'      => 'Store URL',
                'email'    => 'Store Email',
                'currency' => 'Store Currency Code',
                'locale'   => 'Store Locale',
                'timezone' => 'Store Timezone'

            ], $legacy_data
        );

        $website_settings = $this->fill_data(
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


        if ($legacy_data->{'Store Valid To'} == '0000-00-00 00:00:00') {
            $deleted_at = date('Y-m-d H:i:s');
        } else {
            $deleted_at = $legacy_data->{'Store Valid To'};
        }




        return (new Store)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Store Key'},

            ], [
                'tenant_id' => $this->tenant->id,
                'code'       => $legacy_data->{'Store Code'},
                'name'       => $legacy_data->{'Store Name'},
                'state'      => $state,
                'data'       => $website_data,
                'settings'   => $website_settings,
                'created_at' => $legacy_data->{'Store Valid From'},
                'deleted_at' => ($state == 'closed' ? $deleted_at : null)
            ]
        );
    }

    function relocate_websites($legacy_data) {


        $website_data = $this->fill_data(
            [
                'locale' => 'Website Locale',


            ], $legacy_data
        );

        $website_settings       = $this->fill_data(
            [
                'google_tag' => 'Website Google Tag Manager Code',
                'zendesk_chat' => 'Website Zendesk Chat Code',


            ], $legacy_data
        );
        $legacy_status_to_state = [
            'InProcess'   => 'creating',
            'Active'      => 'live',
            'Maintenance' => 'maintenance',
            'Closed'      => 'closed'
        ];


        $state = $legacy_status_to_state[$legacy_data->{'Website Status'}];


        if ($legacy_data->{'Website From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $legacy_data->{'Website From'};
        }

        if ($legacy_data->{'Website Launched'} == '0000-00-00 00:00:00' or $legacy_data->{'Website Launched'}=='') {
            $launched_at = null;
        } else {
            $launched_at = $legacy_data->{'Website Launched'};
        }


        $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Website Store Key'});


        return (new Website())->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Website Key'},

            ], [

                'url'       => $legacy_data->{'Website URL'},
                'tenant_id' => $this->tenant->id,
                'store_id' => $store->id,

                'name'       => $legacy_data->{'Website Name'},
                'state'      => $state,
                'data'       => $website_data,
                'settings'   => $website_settings,
                'created_at' => $created_at,
                'launched_at' => $launched_at,
                'deleted_at' => ($state == 'closed' ? gmdate('Y-m-d H:i:s') : null)
            ]
        );
    }




}
