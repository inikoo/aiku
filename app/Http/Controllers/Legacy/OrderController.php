<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 04 Nov 2020 12:03:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Models\Sales\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller {
    use LegacyHelpers;

    private $tax_number_validation;
    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Order::disableAuditing();
        $this->setLegacyDbConnection();
    }

    function sync(Request $request): JsonResponse {

        $request_data = $request->all();

        $this->parseRequest($request_data);

        $sql = "* from `Order Dimension` where `Order Key`=?";
        foreach (
            DB::connection('legacy')->select(
                "select $sql", [
                                 $this->legacy['order_key']
                             ]
            ) as $legacy_data
        ) {

            if ($legacy_data->{'Order State'} != 'InBasket') {
                $order = relocate_order($legacy_data);

                return response()->json($order);

            } else {
                $order = (new Order)->firstWhere('legacy_id', $legacy_data->{'Order Key'});
                if($order){
                    delete_relocated_order($order);
                }


                return response()->json([]);

            }


        }

        return response()->json(['errors' => 'object not found'], 470);


    }


    function update($legacy_id, Request $request) {


    }


}
