<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\CRM\Customer;
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
        $tenant = Tenant::current();

        $legacy_customers_table         = '`Customer Dimension`';
        $legacy_deleted_customers_table = '`Customer Deleted Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {


            $this->set_legacy_connection($tenant->data['legacy']['db']);


            print ('Relocation customers from '.$tenant->subdomain."\n");

            $count_customers_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_customers_table, [])[0];


            $bar = $this->output->createProgressBar($count_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_customers_table, []) as $legacy_data) {

                $this->relocate_customer($legacy_data, $tenant);

                $bar->advance();
            }

            $bar->finish();


            print ('Relocation deleted customers from '.$tenant->subdomain."\n");


            $count_deleted_customers_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_deleted_customers_table, [])[0];


            $bar = $this->output->createProgressBar($count_deleted_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_deleted_customers_table, []) as $raw_legacy_data) {

                if (!$raw_legacy_data->{'Customer Key'}) {
                    continue;
                }

                $legacy_data = json_decode(gzuncompress($raw_legacy_data->{'Customer Deleted Metadata'}));


                $customer = $this->relocate_customer($legacy_data, $tenant);

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


    function relocate_customer($legacy_data, $tenant) {


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


        $store = (new Store)->firstWhere('legacy_id', $legacy_data->{'Customer Store Key'});


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


        if(!$customer->billing_address_id){
            $billing_address=$this->process_instance_address('Customer',$customer->id,'Invoice',$legacy_data);
        }else{
            $billing_address=$customer->billingAddress;

            $_billing_address=$this->get_instance_address_scaffolding('Customer','Invoice',$legacy_data);



            $billing_address->fill($_billing_address->attributesToArray());
            $billing_address->save();
            $customer->addresses()->syncWithoutDetaching([$billing_address->id]);
        }
        $customer->billing_address_id = $billing_address->id;
        $customer->country_id = $billing_address->country_id;
        $customer->save();

        if(!$customer->delivery_address_id){
            $delivery_address=$this->process_instance_address('Customer',$customer->id,'Delivery',$legacy_data);
        }else{
            $delivery_address=$customer->deliveryAddress;

            $_delivery_address=$this->get_instance_address_scaffolding('Customer','Delivery',$legacy_data);


            $delivery_address->fill($_delivery_address->attributesToArray());
            $delivery_address->save();
            $customer->addresses()->syncWithoutDetaching([$delivery_address->id]);
        }
        $customer->delivery_address_id = $delivery_address->id;
        $customer->save();



        return $customer;


    }



}
