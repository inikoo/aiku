<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;

use App\Models\Sales\Adjust;
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


    public function handle() {
        $this->tenant = Tenant::current();

        if (Arr::get($this->tenant->data, 'legacy')) {

            print ('Relocation Stores/Websites '.$this->tenant->slug."\n");
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);

            $sql = " * from `Store Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $legacy_data) {
                $store = relocate_stores($this->tenant, $legacy_data);
                (new Adjust)->firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'type'     => 'legacy'
                    ], ['name' => 'Adjust']
                );
                (new Adjust)->firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'type'     => 'credit'
                    ], ['name' => 'Credit']
                );
                (new Adjust)->firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'type'     => 'refund'
                    ], ['name' => 'Refund']
                );

                relocate_product_categories($store, $legacy_data);
                relocate_product_hierarchy($this->tenant, $store, $legacy_data);


            }

            $sql = " * from `Website Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $legacy_data) {
                relocate_websites($legacy_data);
            }
            $sql = " * from `Charge Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $legacy_data) {
                relocate_charges($legacy_data);
            }
            $sql = " * from `Shipping Zone Schema Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $legacy_data) {
                relocate_shipping_schemas($legacy_data);
            }
            $sql = " * from `Email Campaign Type Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $legacy_data) {
                relocate_email_services($legacy_data);
            }


            $sql = "* from `Category Dimension` where `Category Branch Type`='Root' ";
            foreach (DB::connection('legacy')->select("select ".$sql, []) as $legacy_data) {

                switch ($legacy_data->{'Category Scope'}) {
                    case 'Part':
                        if ($legacy_data->{'Category Code'} == 'FMap') {

                            $root       = relocate_category(
                                $this->tenant, 'Stock', $legacy_data, [
                                                 'Tenant',
                                                 $this->tenant->id
                                             ]
                            );
                            $legacyData = $this->tenant->data;
                            Arr::set($legacyData, 'categories.stocks.families', $root->id);
                            $this->tenant->data = $legacyData;
                            $this->tenant->save();


                        }
                        break;
                    case 'Supplier':
                        relocate_category(
                            $this->tenant, 'Supplier', $legacy_data, [
                                             'Tenant',
                                             $this->tenant->id
                                         ]
                        );
                        break;
                    case 'Invoice':
                        relocate_category(
                            $this->tenant, 'Invoice', $legacy_data, [
                                             'Tenant',
                                             $this->tenant->id
                                         ]
                        );
                        break;
                    case 'Customer':
                        $store = (new Store)->firstwhere('legacy_id', $legacy_data->{'Category Store Key'});
                        relocate_category(
                            $this->tenant, 'Customer', $legacy_data, [
                                             'Store',
                                             $store->id
                                         ]
                        );

                        break;

                }

            }


        }


        return 0;


    }
}
