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
use App\Models\Helpers\Category;
use App\Models\Stores\Product;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateProducts extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:products {--tenant=*}';
    protected $description = 'Relocate legacy products';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $this->tenant = Tenant::current();

        $legacy_products_table = '`Product Dimension`';

        if (Arr::get($this->tenant->data, 'legacy')) {
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            print ('Relocation products from '.$this->tenant->slug."\n");
            $count_products_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_products_table, [])[0];
            $bar                 = $this->output->createProgressBar($count_products_data->num);
            $bar->setFormat('debug');

            $bar->start();

            $max = 1000;
            $total = $count_products_data->num;
            $pages = ceil($total / $max);
            for ($i = 1; $i < ($pages + 1); $i++) {
                $offset = (($i - 1) * $max);

                foreach (DB::connection('legacy')->select("select * from $legacy_products_table limit  $offset, $max  ", []) as $legacy_data) {

                    $product = $this->relocate_products($legacy_data);

                    $_table = ' `Product History Dimension` ';
                    $_where = ' `Product ID` ';

                    foreach (DB::connection('legacy')->select("select * from  $_table where  $_where=?", [$product->legacy_id]) as $legacy_historic_product_data) {

                        $historic_product = relocate_historic_products($legacy_historic_product_data, $product->id,$product->tenant_id);

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
                                'data'  => array_filter(['note'=>$legacy_product_stock_data->{'Product Part Note'}])
                            ];
                        }
                    }

                    $product->stocks()->sync($product_stock_data);
                    $bar->advance();
                }


            }




            $bar->finish();
            print "\n";

        }


        return 0;


    }


    function relocate_products($legacy_data) {

        $stock_data = fill_legacy_data(
            [], $legacy_data
        );

        $stock_settings = fill_legacy_data(
            [], $legacy_data
        );

        $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Product Store Key'});


        //print_r($legacy_data);


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


        $units = $legacy_data->{'Product Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        if ($legacy_data->{'Product Valid From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $legacy_data->{'Product Valid From'};
        }


        $imagesModelData = $this->get_images_data(
            [
                'object'     => 'Product',
                'object_key' => $legacy_data->{'Product ID'},

            ]
        );

        $attachmentModelData = $this->get_attachments_data(
            [
                'object'     => 'Product',
                'object_key' => $legacy_data->{'Product ID'},

            ]
        );

        $product= (new Product)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Product ID'},
            ], [
                'tenant_id' => $this->tenant->id,
                'store_id'  => $store->id,

                'code' => $legacy_data->{'Product Code'},
                'name' => $legacy_data->{'Product Name'},

                'unit_price' => $legacy_data->{'Product Price'} / $units,
                'units'      => $units,

                'status' => $status,
                'state'  => $state,

                'data'       => $stock_data,
                'settings'   => $stock_settings,
                'created_at' => $created_at,
            ]
        );

        $this->sync_images($product,$imagesModelData, function ($_scope){
            $scope = 'marketing';
            if ($_scope== '') {
                $scope = 'marketing';
            }
            return $scope;
        });


        $this->sync_attachments(
            $product, $attachmentModelData, function ($_scope) {
            switch ($_scope) {
                default:
                    return strtolower($_scope);
            }
        }
        );

        $sql = "C.`Category Key` from `Category Bridge` B  left join `Category Dimension` C on (B.`Category Key`=C.`Category Key`) where `Category Branch Type`='Head' and `Subject`='Product' and `Subject Key`=?";
        foreach (DB::connection('legacy')->select("select $sql", [$legacy_data->{'Product ID'}]) as $legacy_category_data) {
            $category=Category::firstWhere('legacy_id', $legacy_category_data->{'Category Key'});
            if($category){
                $category->products()->attach($product->id);
            }
        }

        return $product;
    }


}
