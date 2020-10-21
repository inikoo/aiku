<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:32:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Models\Distribution\Stock;
use App\Models\Distribution\Location;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\Traits\LegacyHelpers;

class StockController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Stock::disableAuditing();
    }

    function sync(Request $request) {

        $request_data          = $request->all();

        $this->parseRequest($request_data);
        $this->object_parameters['data']     = $this->data;
        $this->object_parameters['settings'] = $this->settings;
        $this->object_parameters['tenant_id'] = app('currentTenant')->id;

        $stock = (new Stock)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );

        $stock=$this->sync_locations($stock);

        return response()->json($stock, 200);

    }

    function update($legacy_id,Request $request) {

        $this->parseRequest($request->all());
        if($stock = Stock::withTrashed()->firstWhere('legacy_id', $legacy_id)){
            $stock=$this->commonUpdate($stock);

            if(isset($this->legacy['locations'])){
                $stock=$this->sync_locations($stock);
            }

            return response()->json($stock, 200);
        }else{
            return response()->json(['errors' => 'object not found'], 470);
        }
    }

    function sync_locations($stock){

        $location_stock_data = [];

        foreach ($this->legacy['parts'] as $parts_data) {

            $legacy_product_stock_data = (object)$parts_data;
            if ($location = (new Location)->firstWhere('legacy_id', $legacy_product_stock_data->{'Location Key'})) {
                $location_stock_data[$location->id] = [
                    'quantity' => $stock->packed_in * $legacy_product_stock_data->{'Quantity On Hand'}];

            }
        }

        $stock->locations()->sync($location_stock_data);
        $stock = $stock->fresh();

        return $stock;

    }

}
