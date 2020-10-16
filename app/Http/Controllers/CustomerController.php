<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 22:38:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class CustomerController extends Controller {


    private $tax_number_validation;
    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Customer::disableAuditing();


    }

    function create(Request $request) {


        $this->parseRequest($request);

        $this->object_parameters['data']     = $this->data;
        $this->object_parameters['settings'] = $this->settings;
        $this->object_parameters['data']     = $this->data;


        $store                                = (new Store)->firstWhere('legacy_id', $$this->legacy['store_key']);
        $this->object_parameters['tenant_id'] = app('currentTenant')->id;
        $this->object_parameters['store_id']  = $store->id;


        $customer = (new Customer)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );


        $billing_address  = $this->get_address('Customer', $customer->id, $this->legacy['billing_address']);
        $delivery_address = $this->get_address('Customer', $customer->id, $this->legacy['delivery_address']);

        $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);

        return response()->json($customer, 200);


    }


    function update($legacy_id, Request $request) {

        $this->parseRequest($request);


        $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_id);


        if (!$customer) {
            return response()->json(['errors' => 'object not found'], 422);

        }


        if (isset($this->legacy['billing_address'])) {


            $billing_address  = $this->get_address('Customer', $customer->id, $this->legacy['billing_address']);
            $delivery_address = $customer->deliveryAddress;


            $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);

        } elseif (isset($this->legacy['delivery_address'])) {

            $delivery_address = $this->get_address('Customer', $customer->id, $this->legacy['delivery_address']);
            $billing_address  = $customer->billingAddress;
            $customer         = legacy_process_addresses($customer, $billing_address, $delivery_address);

        } else {

            $customer->fill($this->object_parameters);


            $data = $this->data + $customer->data;
            if (empty($this->tax_number_validation)) {
                unset($data['tax_number_validation']);
            } else {
                $tax_number_validation         = array_filter($this->tax_number_validation);
                $data['tax_number_validation'] = $tax_number_validation;
            }
            $data = array_filter($data);

            $customer->data     = $data;
            $customer->settings = $this->settings + $customer->settings;
            $customer->save();

        }


        return response()->json($customer, 200);

    }

    function parseRequest($request) {

        $request_data          = $request->all();
        $data                  = Arr::pull($request_data, 'data', false);
        $settings              = Arr::pull($request_data, 'settings', false);
        $tax_number_validation = Arr::pull($request_data, 'tax_number_validation', false);
        $legacy                = Arr::pull($request_data, 'legacy', false);


        $this->data                  = ($data ? array_filter(json_decode($data, true)) : []);
        $this->settings              = ($settings ? array_filter(json_decode($settings, true)) : []);
        $this->tax_number_validation = ($tax_number_validation ? array_filter(json_decode($tax_number_validation, true)) : []);
        $this->legacy                = ($legacy ? array_filter(json_decode($legacy, true)) : []);


        $this->object_parameters = $request_data;

    }

    function get_address($object, $object_key, $address_data) {


        $_address = new Address();
        $_address->fill($address_data);

        return (new Address)->firstOrCreate(
            [
                'checksum'   => $_address->getChecksum(),
                'owner_type' => $object,
                'owner_id'   => $object_key,

            ], [
                'address_line_1'      => $_address->address_line_1,
                'address_line_2'      => $_address->address_line_2,
                'sorting_code'        => $_address->sorting_code,
                'postal_code'         => $_address->postal_code,
                'locality'            => $_address->locality,
                'dependent_locality'  => $_address->dependent_locality,
                'administrative_area' => $_address->administrative_area,
                'country_code'        => $_address->country_code,

            ]
        );

    }


}
