<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:25:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Models\CRM\Customer;
use App\Models\CRM\CustomerPortfolio;
use App\Models\Stores\Product;
use App\Models\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use Illuminate\Support\Facades\DB;


class CustomerController extends Controller {
    use LegacyHelpers;

    private $tax_number_validation;
    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Customer::disableAuditing();
    }

    function sync(Request $request) {

        $request_data                = $request->all();
        $tax_number_validation       = Arr::pull($request_data, 'tax_number_validation', false);
        $this->tax_number_validation = ($tax_number_validation ? array_filter(json_decode($tax_number_validation, true)) : []);


        $this->parseRequest($request_data);

        $this->object_parameters['data']     = $this->data;
        $this->object_parameters['settings'] = $this->settings;

        $store = Store::withTrashed()->firstWhere('legacy_id', $this->legacy['store_key']);


        if ($store->data['type'] == 'dropshipping') {
            $this->object_parameters['data'] = [
                'clients'         => 0,
                'portfolio_items' => 0
            ];
        }

        $this->object_parameters['tenant_id'] = app('currentTenant')->id;
        $this->object_parameters['store_id']  = $store->id;


        $customer = (new Customer)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );


        $billing_address  = legacy_get_address('Customer', $customer->id, $this->legacy['billing_address']);
        $delivery_address = legacy_get_address('Customer', $customer->id, $this->legacy['delivery_address']);

        $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);

        return response()->json($customer, 200);


    }


    function update($legacy_id, Request $request) {

        $this->parseRequest($request->all());

        $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_id);


        if (!$customer) {
            return response()->json(['errors' => 'object not found'], 470);
        }


        if (isset($this->legacy['billing_address'])) {


            $billing_address  = legacy_get_address('Customer', $customer->id, $this->legacy['billing_address']);
            $delivery_address = $customer->deliveryAddress;


            $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);

        } elseif (isset($this->legacy['delivery_address'])) {

            $delivery_address = legacy_get_address('Customer', $customer->id, $this->legacy['delivery_address']);
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

    function sync_portfolio($legacy_customer_id, $legacy_product_id, Request $request) {

        $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_customer_id);
        if (!$customer) {
            return response()->json(['errors' => 'object not found'], 470);
        }
        $product = (new Product)->firstWhere('legacy_id', $legacy_product_id);

        if (!$product) {
            return response()->json(['errors' => 'object not found'], 470);
        }

        $request_data = json_decode($request->all()['legacy'], true);


        $customerPortfolioItem = CustomerPortfolio::withTrashed()->updateOrCreate(
            [
                'legacy_id' => $request_data['Customer Portfolio Key'],

            ], [
                'tenant_id'   => app('currentTenant')->id,
                'customer_id' => $customer->id,
                'product_id'  => $product->id,
                'code'        => Arr::get($request_data, 'Customer Portfolio Reference'),
                'created_at'  => Arr::get($request_data, 'Customer Portfolio Creation Date'),
                'deleted_at'  => ((Arr::get($request_data, 'Customer Portfolio Customers State') == 'Removed' and Arr::get($request_data, 'Customer Portfolio Removed Date') != '') ? Arr::get($request_data, 'Customer Portfolio Removed Date') : null),


            ]
        );

        return response()->json($customerPortfolioItem, 200);


    }

    function updateBasket($legacy_id, Request $request) {


        $this->parseRequest($request->all());
        $customer = (new Customer)->firstWhere('legacy_id', $legacy_id);
        if ($customer) {

            $database_settings = data_get(config('database.connections'), 'mysql');
            data_set($database_settings, 'database', app('currentTenant')->data['legacy']['db']);
            config(['database.connections.legacy' => $database_settings]);
            DB::connection('legacy');
            relocate_basket($legacy_id, $customer->basket);

            return response()->json([], 200);
        } else {
            return response()->json(['errors' => 'object not found'], 470);
        }


    }

}
