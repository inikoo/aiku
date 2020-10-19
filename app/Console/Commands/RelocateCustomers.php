<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use Exception;
use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;
use App\Models\CRM\CustomerPortfolio;
use App\Models\Helpers\Address;
use App\Models\Sales\BasketTransaction;
use App\Models\Stores\Product;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateCustomers extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:customers {--tenant=*}';
    protected $description = 'Relocate legacy customers';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $this->tenant = Tenant::current();

        $legacy_customers_table         = '`Customer Dimension`';
        $legacy_deleted_customers_table = '`Customer Deleted Dimension`';

        if (Arr::get($this->tenant->data, 'legacy')) {


            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            print ('Relocation customers from '.$this->tenant->subdomain."\n");


            $count_customers_data = DB::connection('legacy')->select("select count(*) as num from $legacy_customers_table", [])[0];
            $bar                  = $this->output->createProgressBar($count_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();
            $max   = 1000;
            $total = $count_customers_data->num;
            $pages = ceil($total / $max);
            for ($i = 1; $i < ($pages + 1); $i++) {
                $offset = (($i - 1) * $max);
                foreach (DB::connection('legacy')->select("select * from $legacy_customers_table  limit $offset, $max ", []) as $legacy_data) {

                    $this->relocate_customer($legacy_data);

                    $bar->advance();
                }
            }


            $bar->finish();


            print ('Relocation deleted customers from '.$this->tenant->subdomain."\n");


            $count_deleted_customers_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_deleted_customers_table, [])[0];


            $bar = $this->output->createProgressBar($count_deleted_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_deleted_customers_table, []) as $raw_legacy_data) {

                if (!$raw_legacy_data->{'Customer Key'}) {
                    continue;
                }
                if ($raw_legacy_data->{'Customer Deleted Metadata'} == '') {
                    continue;
                }

                $legacy_data = json_decode(gzuncompress($raw_legacy_data->{'Customer Deleted Metadata'}));


                $customer = $this->relocate_customer($legacy_data);

                $customer->status     = 'deleted';
                $customer->state      = 'deleted';
                $customer->deleted_at = $raw_legacy_data->{'Customer Deleted Date'};
                $customer->save();


                $bar->advance();
            }

            $bar->finish();
            print "\n";


        }


        return 0;


    }


    function relocate_customer($legacy_data) {


        $customer_data = $this->fill_data(
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


        $customer_settings = $this->fill_data(
            [
                'can_send.newsletter'       => 'Customer Send Newsletter',
                'can_send.email_marketing'  => 'Customer Send Email Marketing',
                'can_send.postal_marketing' => 'Customer Send Postal Marketing',

            ], $legacy_data, 'strtolower'
        );


        $customer_data = $this->elementsToLower(
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

        $imagesModelData = $this->get_images_data(
            [
                'object'     => 'Customer',
                'object_key' => $legacy_data->{'Customer Key'},

            ]
        );

        $customer = Customer::withTrashed()->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Customer Key'},

            ], [
                'tenant_id'  => $this->tenant->id,
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

        $this->sync_images(
            $customer, $imagesModelData, function ($_scope) {
            $scope = 'profile';
            if ($_scope == '') {
                $scope = 'profile';
            }

            return $scope;
        }
        );


        $billing_address  = $this->process_instance_address('Customer', $customer->id, 'Invoice', $legacy_data);
        $delivery_address = $this->process_instance_address('Customer', $customer->id, 'Delivery', $legacy_data);


        $customer = legacy_process_addresses($customer, $billing_address, $delivery_address);


        if ($store->data['type'] == 'dropshipping') {

            $this->relocate_customer_client($customer);
            $this->relocate_customer_portfolio($customer);

        }else{
            $this->relocate_basket($customer->basket);

        }


        return $customer;


    }

    function relocate_basket($basket) {



        if ($basket->parent_type == 'Customer') {
            $legacy_column_name = 'Order Customer Key';
        } else {
            $legacy_column_name = 'Order Customer Client Key';

        }
        $oldTransactions = $basket->transactions->pluck('id')->all();

        $sql = " * from  `Order Transaction Fact` OTF  left join `Order Dimension` O on (O.`Order Key`=OTF.`Order Key`)   where `$legacy_column_name`=? and `Order State`=?";

        foreach (
            DB::connection('legacy')->select(
                'select '.$sql, [
                                  $basket->parent_id,
                                  'InBasket'
                              ]
            ) as $otf_data
        ) {


            if ($basketItem = (new BasketTransaction)->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'Product')->first()) {
                $basketItem->fill(
                    [
                        'quantity'  => $otf_data->{'Order Quantity'},
                        'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                        'net'       => $otf_data->{'Order Transaction Amount'},
                        'data'      => []
                    ]
                );
                $basketItem->save();
            } else {

                $product = (new Product())->firstWhere('legacy_id', $otf_data->{'Product ID'});

                $basketItems = new BasketTransaction(
                    [
                        'basket_id' => $basket->id,

                        'tenant_id' => $this->tenant->id,
                        'quantity'  => $otf_data->{'Order Quantity'},
                        'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                        'net'       => $otf_data->{'Order Transaction Amount'},


                        'legacy_id' => $otf_data->{'Order Transaction Fact Key'},
                        'data'      => []
                    ]
                );
                $product->basketTransactions()->save($basketItems);
            }


            $basket->updateTotals();


        }

        $sql = " * from `Order No Product Transaction Fact` OTF left join `Order Dimension` O on (O.`Order Key`=OTF.`Order Key`)  where `$legacy_column_name`=? and `Order State`=? ";
        foreach (
            DB::connection('legacy')->select(
                "select ".$sql, [
                                  $basket->parent_id,
                                  'InBasket'
                              ]
            ) as $onptf_data
        ) {


            $transaction_data = $this->get_transaction_data($onptf_data);


            if ($basketItem = (new BasketTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first()) {
                $basketItem->fill(
                    [
                        'quantity'    => 1,
                        'discounts'   => $onptf_data->{'Transaction Total Discount Amount'},
                        'net'         => $onptf_data->{'Transaction Net Amount'},
                        'tax_band_id' => $transaction_data['tax_band_id'],
                        'data'        => []
                    ]
                );
                $basketItem->save();
            } else {


                $basketItem = new BasketTransaction(
                    [

                        'basket_id'        => $basket->id,
                        'tenant_id'        => $this->tenant->id,
                        'transaction_type' => $transaction_data['type'],
                        'transaction_id'   => $transaction_data['id'],
                        'quantity'         => 1,
                        'discounts'        => $onptf_data->{'Transaction Total Discount Amount'},
                        'net'              => $onptf_data->{'Transaction Net Amount'},
                        'tax_band_id'      => $transaction_data['tax_band_id'],

                        'legacy_id' => $onptf_data->{'Order No Product Transaction Fact Key'},

                        'data' => []
                    ]
                );
                $basketItem->save();
            }


        }

        $basket = $basket->fresh();

        foreach (array_diff($oldTransactions, $basket->transactions->pluck('id')->all()) as $transactionToDelete) {
            if ($transaction = (new BasketTransaction())->find($transactionToDelete)) {
                try {
                    $transaction->delete();
                } catch (Exception $e) {
                }
            }

        }

    }


    function relocate_customer_client($customer) {

        $_table = '`Customer Client Dimension`';
        $_where = '`Customer Client Customer Key`';

        foreach (DB::connection('legacy')->select("select * from $_table where $_where=?", [$customer->legacy_id]) as $legacy_data) {


            $metadata   = json_decode($legacy_data->{'Customer Client Metadata'}, true);
            $deleted_at = null;
            if ($legacy_data->{'Customer Client Status'} == 'Inactive') {
                $deleted_at = $metadata['deactivated_date'];
            }

            $customer_client_data = $this->fill_data(
                [
                    'contact' => 'Customer Client Main Contact Name',
                    'company' => 'Customer Client Company Name',
                    'mobile'  => 'Customer Client Main Plain Mobile',
                    'phone'   => 'Customer Client Main Plain Telephone',
                    'email'   => 'Customer Client Main Plain Email',


                ], $legacy_data
            );

            $customerClient = CustomerClient::withTrashed()->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Customer Client Key'},

                ], [
                    'tenant_id'   => $this->tenant->id,
                    'customer_id' => $customer->id,
                    'code'        => $legacy_data->{'Customer Client Code'},
                    'name'        => $legacy_data->{'Customer Client Name'},
                    'data'        => $customer_client_data,
                    'created_at'  => $legacy_data->{'Customer Client Creation Date'},
                    'deleted_at'  => $deleted_at,


                ]
            );

            $oldAddressId = $customerClient->deliery_id;

            $delivery_address = $this->process_instance_address('CustomerClient', $customerClient->id, 'Contact', $legacy_data);

            $customerClient->delivery_address_id = $delivery_address->id;
            $customerClient->save();
            if ($oldAddressId and $delivery_address->id != $oldAddressId) {
                if ($address = (new Address)->find($oldAddressId)) {
                    $address->deleteIfOrphan();
                }
            }

            $this->relocate_basket($customerClient->basket);


        }


    }

    function relocate_customer_portfolio($customer) {

        $_table = '`Customer Portfolio Fact`';
        $_where = '`Customer Portfolio Customer Key`';

        foreach (DB::connection('legacy')->select("select * from $_table where $_where=?", [$customer->legacy_id]) as $legacy_data) {


            $product = (new Product)->firstWhere('legacy_id', $legacy_data->{'Customer Portfolio Product ID'});
            CustomerPortfolio::withTrashed()->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Customer Portfolio Key'},

                ], [
                    'tenant_id'   => $this->tenant->id,
                    'customer_id' => $customer->id,
                    'product_id'  => $product->id,
                    'code'        => $legacy_data->{'Customer Portfolio Reference'},
                    'created_at'  => $legacy_data->{'Customer Portfolio Creation Date'},
                    'deleted_at'  => (($legacy_data->{'Customer Portfolio Customers State'} == 'Removed' and $legacy_data->{'Customer Portfolio Removed Date'} != '') ? $legacy_data->{'Customer Portfolio Removed Date'} : null),


                ]
            );


        }


    }

}
