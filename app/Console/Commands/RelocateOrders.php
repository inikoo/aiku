<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\CRM\Customer;
use App\Models\Sales\Order;
use App\Models\Stores\Store;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateOrders extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:orders {--tenant=*}';
    protected $description = 'Relocate legacy orders';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $_table = '`Order Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('Relocation orders from '.$tenant->subdomain." ".$tenant->data['legacy']['db']."  \n");

            $this->set_legacy_connection($tenant->data['legacy']['db']);

            $count_data = DB::connection('legacy')->select("select count(*) as num from".' '.$_table, [])[0];


            $bar = $this->output->createProgressBar($count_data->num);

            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {



                $order_data = $this->fill_data(
                    [

                    ], $legacy_data
                );


                $store    = (new Store)->firstWhere('legacy_id', $legacy_data->{'Order Store Key'});
                $customer =  Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Key'});



                //enum('InBasket','InProcess','InWarehouse','PackedDone','Approved','Dispatched','Cancelled')

                $status = 'Processing';
                $state  = null;
                switch ($legacy_data->{'Order State'}) {
                    case 'InBasket':
                        $state  = 'basket';
                        $status = 'basket';
                        break;
                    case 'InProcess':
                        $state = 'submitted';
                        break;
                    case 'InWarehouse':
                        $state = 'picking';
                        break;
                    case 'PackedDone':
                        $state = 'packed';
                        break;

                    case 'Approved':
                        $state  = 'packed';
                        $status = 'Invoiced';
                        break;
                    case 'Dispatched':
                        $state  = 'dispatched';
                        $status = 'Invoiced';
                        break;
                    case 'Cancelled':
                        $status = 'Cancelled';
                        break;

                }




                $order = (new Order)->updateOrCreate(
                    [
                        'legacy_id' => $legacy_data->{'Order Key'},

                    ], [
                        'tenant_id'   => $tenant->id,
                        'store_id'    => $store->id,
                        'customer_id' => $customer->id,

                        'number' => $legacy_data->{'Order Public ID'},
                        'total'  => $legacy_data->{'Order Total Amount'},
                        'net'  => $legacy_data->{'Order Total Net Amount'},
                        'payment'  => $legacy_data->{'Order Payments Amount'},



                        'weight'  => $legacy_data->{'Order Estimated Weight'},
                        'items'=>$legacy_data->{'Order Number Items'},


                        'state'  => $state,
                        'status' => $status,
                        'date' => $legacy_data->{'Order Date'},


                        'data'       => $order_data,
                        'created_at' => $legacy_data->{'Order Created Date'},


                    ]
                );


                switch ($legacy_data->{'Order State'}) {
                   /*
                    case 'InBasket':
                        break;

                    case 'InWarehouse':
                        break;
                    case 'PackedDone':
                        break;


                   */
                    case 'InProcess':
                        $order->submitted_at=$legacy_data->{'Order Submitted by Customer Date'};

                        break;
                    case 'InWarehouse':
                        $order->submitted_at=$legacy_data->{'Order Submitted by Customer Date'};
                        $order->warehoused_at=$legacy_data->{'Order Send to Warehouse Date'};

                        break;
                    case 'Approved':
                        $order->warehoused_at=$legacy_data->{'Order Send to Warehouse Date'};

                        $order->submitted_at=$legacy_data->{'Order Submitted by Customer Date'};
                        $order->invoiced_at=$legacy_data->{'Order Invoiced Date'};
                        break;
                    case 'Dispatched':
                        $order->warehoused_at=$legacy_data->{'Order Send to Warehouse Date'};

                        $order->submitted_at=$legacy_data->{'Order Submitted by Customer Date'};

                        $order->invoiced_at=$legacy_data->{'Order Invoiced Date'};
                        $order->dispatched_at=$legacy_data->{'Order Dispatched Date'};

                        break;
                    case 'Cancelled':
                        $order->submitted_at=$legacy_data->{'Order Submitted by Customer Date'};
                        $order->warehoused_at=$legacy_data->{'Order Send to Warehouse Date'};

                        $order->cancelled_at=$legacy_data->{'Order Cancelled Date'};

                        break;

                }

                $order->save();

                $bar->advance();
            }

            $bar->finish();
            print "\n";

        }


        return 0;


    }


}
