<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:43:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use App\Models\Distribution\Stock;
use App\Models\Stores\Product;
use App\Models\Stores\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Product::disableAuditing();
    }

    function sync(Request $request) {

        $request_data = $request->all();

        $this->parseRequest($request_data);
        $this->object_parameters['data']      = $this->data;
        $this->object_parameters['settings']  = $this->settings;
        $this->object_parameters['tenant_id'] = app('currentTenant')->id;

        $store = Store::withTrashed()->firstWhere('legacy_id', $this->legacy['store_key']);

        $this->object_parameters['store_id'] = $store->id;


        $product = (new Product)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );


        foreach ($this->legacy['historic_products'] as $historic_product_data) {
            $historic_product = relocate_historic_products((object)$historic_product_data, $product->id);

            if ($this->legacy['product_historic_key'] == $historic_product_data['Product Key']) {
                $product->product_historic_variation_id = $historic_product->id;
                $product->save();
            }


        }
        $product=$this->sync_parts($product);
        return response()->json($product, 200);


    }


    function update($legacy_id, Request $request) {

        $this->parseRequest($request->all());
        if ($product = (new Product)->firstWhere('legacy_id', $legacy_id)) {


            $product = $this->commonUpdate($product);


            if(isset($this->legacy['parts'])){
                $product=$this->sync_parts($product);
            }


            return response()->json($product, 200);

        } else {
            return response()->json(['errors' => 'object not found'], 470);
        }
    }


    function sync_parts($product){

        $product_stock_data = [];

        foreach ($this->legacy['parts'] as $parts_data) {

            $legacy_product_stock_data = (object)$parts_data;
            if ($stock = (new Stock)->firstWhere('legacy_id', $legacy_product_stock_data->{'Product Part Part SKU'})) {
                $product_stock_data[$stock->id] = [
                    'ratio' => $stock->packed_in * $legacy_product_stock_data->{'Product Part Ratio'},
                    'data'  => array_filter(['note' => $legacy_product_stock_data->{'Product Part Note'}])
                ];
            }
        }

        $product->stocks()->sync($product_stock_data);
        $product = $product->fresh();

        return $product;

    }


}
