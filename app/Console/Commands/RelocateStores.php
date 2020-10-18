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
use App\Models\Sales\Charge;
use App\Models\Sales\ShippingSchema;
use App\Models\Sales\ShippingZone;
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

        $legacy_stores_table           = '`Store Dimension`';
        $legacy_websites_table         = '`Website Dimension`';
        $legacy_charges_table          = '`Charge Dimension`';
        $legacy_shipping_schemas_table = '`Shipping Zone Schema Dimension`';

        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation Stores/Websites '.$this->tenant->subdomain."\n");
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_stores_table, []) as $legacy_data) {
                $this->relocate_stores($legacy_data);
            }


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_websites_table, []) as $legacy_data) {
                $this->relocate_websites($legacy_data);
            }

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_charges_table, []) as $legacy_data) {
                $this->relocate_charges($legacy_data);
            }

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_shipping_schemas_table, []) as $legacy_data) {
                $this->relocate_shipping_schemas($legacy_data);
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
                'timezone' => 'Store Timezone',
                'type'     => 'Store Type'

            ], $legacy_data
        );

        $website_data['type'] = strtolower($website_data['type']);

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
                'tenant_id'  => $this->tenant->id,
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
                'google_tag'   => 'Website Google Tag Manager Code',
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

        if ($legacy_data->{'Website Launched'} == '0000-00-00 00:00:00' or $legacy_data->{'Website Launched'} == '') {
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
                'store_id'  => $store->id,

                'name'        => $legacy_data->{'Website Name'},
                'state'       => $state,
                'data'        => $website_data,
                'settings'    => $website_settings,
                'created_at'  => $created_at,
                'launched_at' => $launched_at,
                'deleted_at'  => ($state == 'closed' ? gmdate('Y-m-d H:i:s') : null)
            ]
        );
    }

    function relocate_charges($legacy_data) {


        $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Charge Store Key'});


        $charge_data = $this->fill_data(
            [
                'description'        => 'Charge Description',
                'public_description' => 'Charge Public Description',


            ], $legacy_data
        );


        $charge_settings = $this->fill_data(
            [
                'amount' => 'Charge Metadata',


            ], $legacy_data
        );


        if ($legacy_data->{'Charge Terms Type'} == 'Order Items Net Amount') {
            $where_field = 'itemsNet';
        } else {
            $where_field = '';
        }

        $where_metadata = preg_split('/;/', $legacy_data->{'Charge Terms Metadata'});

        $charge_settings['where'] = [
            $where_field,
            $where_metadata[0],
            $where_metadata[1]
        ];


        return (new Charge)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Charge Key'},

            ], [
                'tenant_id'  => $this->tenant->id,
                'store_id'   => $store->id,
                'type'       => strtolower($legacy_data->{'Charge Scope'}),
                'status'     => $legacy_data->{'Charge Active'} == 'Yes',
                'name'       => $legacy_data->{'Charge Name'},
                'data'       => $charge_data,
                'settings'   => $charge_settings,
                'created_at' => $legacy_data->{'Charge Begin Date'},
            ]
        );
    }

    function relocate_shipping_schemas($legacy_data) {


        $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Shipping Zone Schema Store Key'});


        $shipping_schema_data = $this->fill_data(
            [
                'type' => 'Shipping Zone Schema Type',
            ], $legacy_data, 'strtolower'
        );


        $shipping_schema_settings = $this->fill_data(
            [], $legacy_data
        );


        $shipping_schema = (new ShippingSchema())->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Shipping Zone Schema Key'},

            ], [
                'tenant_id'  => $this->tenant->id,
                'store_id'   => $store->id,
                'status'     => $legacy_data->{'Shipping Zone Schema Store State'} == 'Active',
                'name'       => $legacy_data->{'Shipping Zone Schema Label'},
                'data'       => $shipping_schema_data,
                'settings'   => $shipping_schema_settings,
                'created_at' => $legacy_data->{'Shipping Zone Schema Creation Date'},
            ]
        );

        $shipping_zones_table = '`Shipping Zone Dimension`';
        $_where               = '`Shipping Zone Shipping Zone Schema Key`';
        foreach (DB::connection('legacy')->select("select * from  $shipping_zones_table where  $_where=?", [$legacy_data->{'Shipping Zone Schema Key'}]) as $legacy_shipping_zone_data) {


            $shipping_schema_data = $this->fill_data(
                [
                    'name' => 'Shipping Zone Name',
                ], $legacy_shipping_zone_data
            );


            $price_data                        = json_decode($legacy_shipping_zone_data->{'Shipping Zone Price'}, true);

            if($price_data['type']=='TBC'){
                $shipping_schema_settings['price'] = [
                    'type'   => 'tbp',
                    'metric' => '',
                    'rules'  => ''
                ];
            }else{
                $shipping_schema_settings['price'] = [
                    'type'   => 'steps',
                    'metric' => 'itemsNet',
                    'rules'  => $price_data['steps']
                ];
            }



            $shipping_schema_settings['territories'] = json_decode($legacy_shipping_zone_data->{'Shipping Zone Territories'});

            (new ShippingZone())->updateOrCreate(
                [
                    'legacy_id' => $legacy_shipping_zone_data->{'Shipping Zone Key'},

                ], [
                    'tenant_id'          => $this->tenant->id,
                    'shipping_schema_id' => $shipping_schema->id,
                    'status'             => $legacy_shipping_zone_data->{'Shipping Zone Active'} == 'Yes',
                    'code'               => $legacy_shipping_zone_data->{'Shipping Zone Code'},
                    'data'               => $shipping_schema_data,
                    'settings'           => $shipping_schema_settings,
                    'created_at'         => $legacy_shipping_zone_data->{'Shipping Zone Creation Date'},
                    'precedence'         => $legacy_shipping_zone_data->{'Shipping Zone Position'}
                ]
            );

        }

        return $shipping_schema;


    }


}
