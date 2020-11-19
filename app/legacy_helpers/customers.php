<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 00:48:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\CRM\Customer;
use App\Models\CRM\CustomerPortfolio;
use App\Models\Helpers\Category;
use App\Models\Stores\Product;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\DB;

function relocate_customer($tenant, $legacy_data) {


    $customer_data = fill_legacy_data(
        [
            'contact'             => 'Customer Main Contact Name',
            'company'             => 'Customer Company Name',
            'registration_number' => 'Customer Registration Number',

            'tax_number'                    => 'Customer Tax Number',
            'tax_number_validation.valid'   => 'Customer Tax Number Valid',
            'tax_number_validation.source'  => 'Customer Tax Number Validation Source',
            'tax_number_validation.date'    => 'Customer Tax Number Validation Date',
            'tax_number_validation.message' => 'Customer Tax Number Validation Message',

            'tax_number_validation.registered_name'    => 'Customer Tax Number Registered Name',
            'tax_number_validation.registered_address' => 'Customer Tax Number Registered Address',
            'website'                                  => 'Customer Website'


        ], $legacy_data
    );


    $customer_settings = fill_legacy_data(
        [
            'can_send.newsletter'       => 'Customer Send Newsletter',
            'can_send.email_marketing'  => 'Customer Send Email Marketing',
            'can_send.postal_marketing' => 'Customer Send Postal Marketing',

        ], $legacy_data, 'strtolower'
    );


    $customer_data = elementsToLower(
        [
            'tax_number_validation.valid',
            'tax_number_validation.source'
        ], $customer_data
    );

    if ($legacy_data->{'Customer Tax Number'} == '') {
        unset($customer_data['tax_number_validation']);
    }


    //state->  registered,new,active,losing,lost,deleted

    $status = 'approved';
    $state  = 'active';
    if ($legacy_data->{'Customer Type by Activity'} == 'Rejected') {
        $status = 'rejected';
    } elseif ($legacy_data->{'Customer Type by Activity'} == 'ToApprove') {
        $state  = 'registered';
        $status = 'pending-approval';
    } elseif ($legacy_data->{'Customer Type by Activity'} == 'Losing') {
        $state = 'losing';
    } elseif ($legacy_data->{'Customer Type by Activity'} == 'Lost') {
        $state = 'lost';
    }


    $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Customer Store Key'});


    if ($store->data['type'] == 'dropshipping') {
        $customer_data['dropshipping'] = [
            'clients'         => 0,
            'portfolio_items' => 0
        ];
    }

    $imagesModelData = get_images_data(
        $tenant, [
                   'object'     => 'Customer',
                   'object_key' => $legacy_data->{'Customer Key'},

               ]
    );

    $customer = Customer::withTrashed()->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Customer Key'},

        ], [
            'tenant_id'  => $tenant->id,
            'name'       => $legacy_data->{'Customer Name'},
            'email'      => $legacy_data->{'Customer Main Plain Email'},
            'mobile'     => $legacy_data->{'Customer Main Plain Mobile'},
            'state'      => $state,
            'status'     => $status,
            'data'       => $customer_data,
            'settings'   => $customer_settings,
            'created_at' => $legacy_data->{'Customer First Contacted Date'},
            'store_id'   => $store->id,

        ]
    );

    sync_images(
        $customer, $imagesModelData, function ($_scope) {
        $scope = 'profile';
        if ($_scope == '') {
            $scope = 'profile';
        }

        return $scope;
    }
    );


    $billing_address  = process_instance_address_legacy('Customer', $customer->id, 'Invoice', $legacy_data);
    $delivery_address = process_instance_address_legacy('Customer', $customer->id, 'Delivery', $legacy_data);


    $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);


    if ($store->data['type'] == 'dropshipping') {

        relocate_customer_client($tenant, $customer);
        relocate_customer_portfolio($tenant, $customer);

    } else {
        try {
            relocate_basket($legacy_data->{'Customer Key'}, $customer->basket);
        } catch (Exception $e) {
            //
        }
    }

    $sql = "C.`Category Key` from `Category Bridge` B  left join `Category Dimension` C on (B.`Category Key`=C.`Category Key`) where `Category Branch Type`='Head' and `Subject`='Customer' and `Subject Key`=?";
    foreach (DB::connection('legacy')->select("select $sql", [$legacy_data->{'Customer Key'}]) as $legacy_category_data) {
        $category = Category::firstWhere('legacy_id', $legacy_category_data->{'Category Key'});
        if ($category) {
            $category->customers()->syncWithoutDetaching([$customer->id]);
        }
    }

    return $customer;


}

function relocate_customer_portfolio($tenant, $customer) {

    $_table = '`Customer Portfolio Fact`';
    $_where = '`Customer Portfolio Customer Key`';

    foreach (DB::connection('legacy')->select("select * from $_table where $_where=?", [$customer->legacy_id]) as $legacy_data) {

        $product = (new Product)->firstWhere('legacy_id', $legacy_data->{'Customer Portfolio Product ID'});
        CustomerPortfolio::withTrashed()->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Customer Portfolio Key'},

            ], [
                'tenant_id'   => $tenant->id,
                'customer_id' => $customer->id,
                'product_id'  => $product->id,
                'code'        => $legacy_data->{'Customer Portfolio Reference'},
                'created_at'  => $legacy_data->{'Customer Portfolio Creation Date'},
                'deleted_at'  => (($legacy_data->{'Customer Portfolio Customers State'} == 'Removed' and $legacy_data->{'Customer Portfolio Removed Date'} != '') ? $legacy_data->{'Customer Portfolio Removed Date'} : null),
            ]
        );
    }
}
