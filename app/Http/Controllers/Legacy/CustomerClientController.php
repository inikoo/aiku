<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:29:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;
use App\Models\Helpers\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerClientController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $data;
    private $legacy;

    public function __construct() {
        CustomerClient::disableAuditing();
        Address::disableAuditing();

    }

    function sync(Request $request) {

        $this->parseRequest($request->all());


        $this->object_parameters['data'] = $this->data;


        $customer = (new Customer)->firstWhere('legacy_id', $this->legacy['customer_key']);
        if (!$customer) {
            return response()->json(
                [
                    'errors'  => 'Parent not found',
                    'parents' => [
                        'Customer' => $this->legacy['customer_key']
                    ]
                ], 471
            );
        }


        $this->object_parameters['tenant_id']   = app('currentTenant')->id;
        $this->object_parameters['customer_id'] = $customer->id;


        $customerClient = (new CustomerClient)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $this->object_parameters
        );

        $customerClient = $this->process_address($customerClient);


        return response()->json($customerClient, 200);
    }

    function update($legacy_id, Request $request) {

        $this->parseRequest($request->all());


        $customerClient = (new CustomerClient)->firstWhere('legacy_id', $legacy_id);


        if (!$customerClient) {
            return response()->json(['errors' => 'object not found'], 470);
        }


        if (isset($this->legacy['delivery_address'])) {


            $customerClient = $this->process_address($customerClient);


        } else {

            $customerClient->fill($this->object_parameters);


            $data = $this->data + $customerClient->data;
            $data = array_filter($data);

            $customerClient->data = $data;
            $customerClient->save();

        }


        return response()->json($customerClient, 200);

    }

    function process_address($customerClient) {

        $delivery_address = legacy_get_address('CustomerClient', $customerClient->id, $this->legacy['delivery_address']);
        $oldAddressId     = $customerClient->deliery_id;

        $customerClient->delivery_address_id = $delivery_address->id;
        $customerClient->save();
        if ($oldAddressId and $delivery_address->id != $oldAddressId) {
            $address = (new Address)->find($oldAddressId);
            if ($address) {
                $address->deleteIfOrphan();
            }
        }

        return $customerClient;

    }

    function updateBasket($legacy_id, Request $request) {

        $this->parseRequest($request->all());
        if ($customerClient = (new CustomerClient)->firstWhere('legacy_id', $legacy_id)) {
            $database_settings = data_get(config('database.connections'), 'mysql');
            data_set($database_settings, 'database', app('currentTenant')->data['legacy']['db']);
            config(['database.connections.legacy' => $database_settings]);
            DB::connection('legacy');

            relocate_basket($legacy_id, $customerClient->basket);

            return response()->json([], 200);
        } else {
            return response()->json(['errors' => 'object not found'], 470);
        }


    }


}
