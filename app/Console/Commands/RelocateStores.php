<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Distribution\Stock;
use App\Models\Stores\Product;
use App\Models\Stores\ProductHistoricVariation;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateStores extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:stores {--tenant=*}';
    protected $description = 'Relocate legacy stores';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $legacy_stores_table   = '`Store Dimension`';
        $legacy_products_table = '`Product Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('The tenant is '.$tenant->subdomain."\n");
            $this->set_legacy_connection($tenant->data['legacy']['db']);


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_stores_table, []) as $legacy_data) {
                $this->relocate_stores($legacy_data, $tenant);
            }
            print ('Relocation products from '.$tenant->subdomain."\n");
            $count_products_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_products_table, [])[0];
            $bar                 = $this->output->createProgressBar($count_products_data->num);
            $bar->setFormat('debug');

            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_products_table.'  order by `Product ID` desc ', []) as $legacy_data) {
                $product = $this->relocate_products($legacy_data, $tenant);

                $_table = ' `Product History Dimension` ';
                $_where = ' `Product ID` ';

                foreach (DB::connection('legacy')->select("select * from  $_table where  $_where=?", [$product->legacy_id]) as $legacy_historic_product_data) {

                    $historic_product = $this->relocate_historic_products($legacy_historic_product_data, $product->id);

                    if ($legacy_data->{'Product Current Key'} == $legacy_historic_product_data->{'Product Key'}) {
                        $product->product_historic_variation_id = $historic_product->id;
                        $product->save();
                    }


                }


                $_table             = ' `Product Part Bridge` ';
                $_where             = ' `Product Part Product ID` ';
                $product_stock_data = [];
                foreach (DB::connection('legacy')->select("select * from  $_table where  $_where=?", [$product->legacy_id]) as $legacy_product_stock_data) {

                    if ($stock = (new Stock)->firstWhere('legacy_id', $legacy_product_stock_data->{'Product Part Part SKU'})) {
                        $product_stock_data[$stock->id] = [
                            'ratio' => $stock->packed_in * $legacy_product_stock_data->{'Product Part Ratio'},
                            'data'  => []
                        ];
                    }
                }

                $product->stocks()->sync($product_stock_data);
                $bar->advance();
            }
            $bar->finish();
            print "\n";

        }


        return 0;


    }

    function relocate_stores($legacy_data, $tenant) {

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


        return (new Store)->updateOrCreate(
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

    function relocate_products($legacy_data, $tenant) {

        $stock_data = $this->fill_data(
            [], $legacy_data
        );

        $stock_settings = $this->fill_data(
            [], $legacy_data
        );

        $store = (new Store)->firstWhere('legacy_id', $legacy_data->{'Product Store Key'});


        $status = true;
        if ($legacy_data->{'Product Status'} == 'Discontinued') {
            $status = false;
        }


        switch ($legacy_data->{'Product Status'}) {
            case 'InProcess':
                $state = 'creating';
                break;
            case 'Discontinuing':
                $state = 'discontinuing';
                break;
            case 'Discontinued':
                $state = 'discontinued';
                break;

            default:
                $state = 'active';
        }


        $units=$legacy_data->{'Product Units Per Case'};
        if($units==0){
            $units=1;
        }

        return (new Product)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Product ID'},
            ], [
                'tenant_id' => $tenant->id,
                'store_id'  => $store->id,

                'code' => $legacy_data->{'Product Code'},
                'name' => $legacy_data->{'Product Name'},

                'unit_price' => $legacy_data->{'Product Price'}/ $units,
                'units'      => $legacy_data->{'Product Units Per Case'},

                'status' => $status,
                'state'  => $state,

                'data'       => $stock_data,
                'settings'   => $stock_settings,
                'created_at' => $legacy_data->{'Product Valid From'},
            ]
        );
    }

    function relocate_historic_products($legacy_data, $product_id) {

        $stock_data = $this->fill_data(
            [
                'code' => 'Product History Code',
                'name' => 'Product History Name'
            ], $legacy_data
        );


        $units=$legacy_data->{'Product History Units Per Case'};
        if($units==0){
            $units=1;
        }

        return (new ProductHistoricVariation)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Product Key'},
            ], [

                'product_id' => $product_id,
                'unit_price' => $legacy_data->{'Product History Price'}/$units,
                'units'      => $units,
                'data' => $stock_data,
                'date' => $legacy_data->{'Product History Valid From'},
            ]
        );
    }


}
