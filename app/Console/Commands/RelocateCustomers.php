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
use App\Models\Helpers\Address;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

        $legacy_customers_table = '`Customer Dimension`';
        $legacy_deleted_customers_table = '`Customer Deleted Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {




            $this->set_legacy_connection($tenant->data['legacy']['db']);


            print ('Relocation customers from '.$tenant->subdomain." ".$tenant->data['legacy']['db']."  \n");

            $count_customers_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_customers_table, [])[0];


            $bar = $this->output->createProgressBar($count_customers_data->num);

            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_customers_table, []) as $legacy_data) {

                $this->relocate_customer($legacy_data,$tenant);

                $bar->advance();
            }

            $bar->finish();



            print ('Relocation deleted customers from '.$tenant->subdomain." ".$tenant->data['legacy']['db']."  \n");


            $count_deleted_customers_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_deleted_customers_table, [])[0];


            $bar = $this->output->createProgressBar($count_deleted_customers_data->num);

            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_deleted_customers_table, []) as $raw_legacy_data) {


                $legacy_data=json_decode(gzuncompress($raw_legacy_data->{'Customer Deleted Metadata'}));


                $customer=$this->relocate_customer($legacy_data,$tenant);
                $customer->status='deleted';
                $customer->state='deleted';

                $customer->deleted_at=$raw_legacy_data->{'Customer Deleted Date'};
                $customer->save();


                $bar->advance();
            }

            $bar->finish();
            print "\n";


        }


        return 0;


    }


    function relocate_customer($legacy_data,$tenant){

        $customer_data = $this->fill_data(
            [

            ], $legacy_data
        );

        $customer_settings = $this->fill_data(
            [

            ], $legacy_data
        );






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


        $customer = (new Customer)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Customer Key'},

            ], [
                'tenant_id'  => $tenant->id,
                'slug'       => Str::kebab(strtolower($legacy_data->{'Customer Name'})),
                'name'       => $legacy_data->{'Customer Name'},
                'email'       => $legacy_data->{'Customer Main Plain Email'},
                'mobile'       => $legacy_data->{'Customer Main Plain Mobile'},
                'state'      => $state,
                'status'     => $status,
                'data'       => $customer_data,
                'settings'   => $customer_settings,
                'created_at' => $legacy_data->{'Customer First Contacted Date'},
                'store_id'   => $store->id,

            ]
        );


        $_billing_address = new Address();

        $_billing_address->address_line_1 = $legacy_data->{'Customer Invoice Address Line 1'};
        $_billing_address->address_line_2 = $legacy_data->{'Customer Invoice Address Line 2'};

        $_billing_address->sorting_code = $legacy_data->{'Customer Invoice Address Sorting Code'};
        $_billing_address->postal_code = $legacy_data->{'Customer Invoice Address Postal Code'};
        $_billing_address->locality = $legacy_data->{'Customer Invoice Address Locality'};
        $_billing_address->dependent_locality = $legacy_data->{'Customer Invoice Address Dependent Locality'};
        $_billing_address->administrative_area = $legacy_data->{'Customer Invoice Address Administrative Area'};


        $_billing_address->country_code   = $legacy_data->{'Customer Invoice Address Country 2 Alpha Code'};

        $_billing_address->checksum = $_billing_address->checksum();


        $billing_address = (new Address)->firstOrCreate(
            [
                'checksum'   => $_billing_address->checksum,
                'owner_type' => 'Customer',
                'owner_id'   => $customer->id,

            ], [
                'address_line_1' => $_billing_address->address_line_1,
                'address_line_2' => $_billing_address->address_line_2,

                'sorting_code'   => $_billing_address->sorting_code,
                'postal_code'   => $_billing_address->postal_code,
                'locality'   => $_billing_address->locality,
                'dependent_locality'   => $_billing_address->dependent_locality,
                'administrative_area'   => $_billing_address->administrative_area,



                'country_code'   => $_billing_address->country_code,

            ]
        );


        $billing_address_id = false;
        foreach ($customer->addresses as $address) {
            if ($address->checksum == $billing_address->checksum) {
                $billing_address_id = $address->id;

            }
        }
        if (!$billing_address_id) {
            $billing_address->save();

            $customer->addresses()->attach($billing_address->id);

            $customer->save();
        }
        $customer->billing_address_id = $billing_address->id;
        $customer->save();

        return $customer;


    }

}
