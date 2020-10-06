<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\CRM\Customer;
use App\Models\Distribution\DeliveryNote;
use App\Models\Distribution\Stock;
use App\Models\HR\Employee;
use App\Models\Sales\Order;
use App\Models\Stores\Product;
use App\Models\Stores\ProductHistoricVariation;
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

        $delivery_notes_table = '`Delivery Note Dimension`';
        $delivery_notes_where = '`Delivery Note Order Key`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('Relocation orders from '.$tenant->subdomain." ".$tenant->data['legacy']['db']."  \n");

            $this->set_legacy_connection($tenant->data['legacy']['db']);

            $count_data = DB::connection('legacy')->select("select count(*) as num from".' '.$_table, [])[0];


            $bar = $this->output->createProgressBar($count_data->num);
            $bar->setFormat('debug');

            $bar->start();

            foreach (DB::connection('legacy')->select("select * from".' '.$_table.'   ', []) as $legacy_data) {
                $_table = ' `Order Transaction Fact` ';
                $_where = ' `Order Key` ';

                if ($legacy_data->{'Order State'} == 'InBasket') {

                    $customer    = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Key'});
                    $basketItems = [];
                    foreach (DB::connection('legacy')->select("select * from  $_table where  $_where=?", [$legacy_data->{'Order Key'}]) as $otf_data) {
                        $product                   = (new Product())->firstWhere('legacy_id', $otf_data->{'Product ID'});
                        $basketItems[$product->id] = [
                            'store_id'    => $customer->store_id,
                            'customer_id' => $customer->id,
                            'tenant_id'   => $tenant->id,
                            'quantity'    => $otf_data->{'Order Quantity'},
                            'legacy_id'   => $otf_data->{'Order Transaction Fact Key'},
                            'data'        => []
                        ];

                    }


                    $customer->basketItems()->sync($basketItems);


                } else {
                    $order = $this->relocate_order($legacy_data, $tenant);


                    $transactions = [];
                    foreach (DB::connection('legacy')->select("select * from  $_table where  $_where=?", [$order->legacy_id]) as $otf_data) {

                        $product_historic_variant = (new ProductHistoricVariation())->firstWhere('legacy_id', $otf_data->{'Product Key'});
                        $product                  = (new Product())->firstWhere('legacy_id', $otf_data->{'Product ID'});

                        $transactions[$product->id] = [

                            'store_id'                      => $order->store_id,
                            'customer_id'                   => $order->customer_id,
                            'tenant_id'                     => $tenant->id,
                            'product_historic_variation_id' => $product_historic_variant->id,
                            'legacy_id'                     => $otf_data->{'Order Transaction Fact Key'},

                            'quantity' => $otf_data->{'Order Quantity'},
                            'data'     => []
                        ];
                    }
                    $order->transactions()->sync($transactions);

                    foreach (DB::connection('legacy')->select("select * from  $delivery_notes_table where  $delivery_notes_where=?", [$order->legacy_id]) as $dn_legacy_data) {

                        if ($dn_legacy_data->{'Delivery Note State'} != 'Cancelled to Restock') {
                            $delivery_note = $this->relocate_delivery_note($dn_legacy_data, $order, $tenant);

                            if ($dn_legacy_data->{'Delivery Note State'} == 'Dispatched' or $dn_legacy_data->{'Delivery Note State'} == 'Cancelled') {


                                $delivery_note->sync_items($this->get_legacy_dispatched_itf($delivery_note), 'delivery_note_items');


                            } else {


                                $delivery_note->sync_items($this->get_legacy_picking_itf($delivery_note), 'pickings');

                            }


                        } else {
                            $this->relocate_return($dn_legacy_data, $order, $tenant);

                        }


                    }


                }

                $bar->advance();
            }

            $bar->finish();
            print "\n";

        }


        return 0;


    }

    function relocate_return($dn_legacy_data, $order, $tenant) {

    }

    function get_legacy_picking_itf($delivery_note) {

        $itf_table = '`Inventory Transaction Fact`';
        $itf_where = '`Delivery Note key`';

        $legacy_picking_itf = [];

        foreach (
            DB::connection('legacy')->select(
                "select * from  $itf_table where   $itf_where=?", [$delivery_note->legacy_id]
            ) as $legacy_picking_itf_data
        ) {


            $stock     = Stock::withTrashed()->firstWhere('legacy_id', $legacy_picking_itf_data->{'Part SKU'});
            $picked_by = null;
            $picked    = null;
            $picked_at = null;
            $locations = [];

            switch ($delivery_note->state) {
                case 'labeled':

                    $state  = 'done';
                    $status = 'packed';


                    $qty = abs($legacy_picking_itf_data->{'Inventory Transaction Quantity'});

                    if ($qty < $legacy_picking_itf_data->{'Required'}) {


                        if ($qty == 0) {
                            $status = 'out_of_stock';
                        } else {
                            $status = 'partially_packed';
                        }

                    }

                    $picked_by = $delivery_note->picker_id;
                    $picked    = $qty * $stock->packed_in;
                    $locations = [
                        $legacy_picking_itf_data->{'Location Key'} => $picked
                    ];
                    if ($legacy_picking_itf_data->{'Date Picked'} != '' and $legacy_picking_itf_data->{'Date Picked'} != '0000-00-00 00:00:00') {
                        $picked_at = $legacy_picking_itf_data->{'Date Picked'};
                    }

                    break;
                case 'waiting':
                    $state  = 'created';
                    $status = 'processing';
                    break;
                default:
                    print "== ".$delivery_note->state."\n";
                    exit;

            }


            $data = [
                'location' => $locations
            ];


            $legacy_picking_itf[$stock->id] = [

                'weight'    => $legacy_picking_itf_data->{'Inventory Transaction Weight'},
                'required'  => $legacy_picking_itf_data->{'Required'} * $stock->packed_in,
                'picked'    => $picked,
                'picked_by' => $picked_by,
                'picked_at' => $picked_at,
                'legacy_id' => $legacy_picking_itf_data->{'Inventory Transaction Key'},
                'state'     => $state,
                'status'    => $status,
                'data'      => $data,

            ];

        }

        return $legacy_picking_itf;

    }

    function get_legacy_dispatched_itf($delivery_note) {

        $itf_table = '`Inventory Transaction Fact`';
        $itf_where = '`Delivery Note Key`';

        $legacy_dispatched_itf = [];


        foreach (
            DB::connection('legacy')->select(
                "select * from  $itf_table where   $itf_where=?", [$delivery_note->legacy_id]
            ) as $legacy_picking_itf_data
        ) {


            $stock = Stock::withTrashed()->firstWhere('legacy_id', $legacy_picking_itf_data->{'Part SKU'});
            $qty   = abs($legacy_picking_itf_data->{'Inventory Transaction Quantity'});

            $was_dispatched = true;

            $status = 'dispatched';


            if ($qty == 0) {
                $was_dispatched = false;
                $status         = 'out_of_stock';
            } elseif ($qty < $legacy_picking_itf_data->{'Required'}) {
                $status = 'partially_dispatched';
            }

            if ($delivery_note->state == 'cancelled') {
                $status = 'cancelled';
            }

            $data = [];

            $legacy_dispatched_itf[$stock->id] = [
                'was_dispatched' => $was_dispatched,
                'status'         => $status,
                'weight'         => $legacy_picking_itf_data->{'Inventory Transaction Weight'},
                'required'       => $legacy_picking_itf_data->{'Required'} * $stock->packed_in,
                'dispatched'     => $qty * $stock->packed_in,

                'legacy_id' => $legacy_picking_itf_data->{'Inventory Transaction Key'},
                'data'      => $data,
            ];

        }


        return $legacy_dispatched_itf;

    }

    function relocate_order($legacy_data, $tenant) {
        $order_data = $this->fill_data(
            [

            ], $legacy_data
        );


        $store    = (new Store)->firstWhere('legacy_id', $legacy_data->{'Order Store Key'});
        $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Key'});


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
                $status = 'invoiced';
                break;
            case 'Dispatched':
                $state  = 'dispatched';
                $status = 'invoiced';
                break;
            case 'Cancelled':
                $status = 'cancelled';
                break;

        }


        $order = (new Order)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Order Key'},

            ], [
                'tenant_id'   => $tenant->id,
                'store_id'    => $store->id,
                'customer_id' => $customer->id,

                'number'  => $legacy_data->{'Order Public ID'},
                'total'   => $legacy_data->{'Order Total Amount'},
                'net'     => $legacy_data->{'Order Total Net Amount'},
                'payment' => $legacy_data->{'Order Payments Amount'},


                'weight' => $legacy_data->{'Order Estimated Weight'},
                'items'  => $legacy_data->{'Order Number Items'},


                'state'  => $state,
                'status' => $status,
                'date'   => $legacy_data->{'Order Date'},


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


            */ case 'InProcess':
            $order->submitted_at = $legacy_data->{'Order Submitted by Customer Date'};

            break;
            case 'InWarehouse':
                $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
                $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};

                break;
            case 'Approved':
                $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};

                $order->submitted_at = $legacy_data->{'Order Submitted by Customer Date'};
                $order->invoiced_at  = $legacy_data->{'Order Invoiced Date'};
                break;
            case 'Dispatched':
                $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};

                $order->submitted_at = $legacy_data->{'Order Submitted by Customer Date'};

                $order->invoiced_at   = $legacy_data->{'Order Invoiced Date'};
                $order->dispatched_at = $legacy_data->{'Order Dispatched Date'};

                break;
            case 'Cancelled':
                $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
                $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};

                $order->cancelled_at = $legacy_data->{'Order Cancelled Date'};

                break;

        }

        $order->save();

        return $order;
    }

    function relocate_delivery_note($legacy_data, $order, $tenant) {


        $order_data = $this->fill_data(
            [

            ], $legacy_data
        );


        $status = 'processing';
        $state  = null;
        switch ($legacy_data->{'Delivery Note State'}) {
            case 'Ready to be Picked':
                $state = 'waiting';
                break;
            case 'Picker Assigned':
                $state = 'assigned';
                break;
            case 'Picking':
                $state = 'picking';
                break;
            case 'Picked':
                $state = 'picked';
                break;
            case 'Packing':
                $state = 'packing';
                break;
            case 'Packed':
                $state = 'packed';
                break;
            case 'Packed Done':
            case 'Approved':
                $state = 'labeled';
                break;
            case 'Dispatched':
                $state  = 'packed';
                $status = 'invoiced';
                break;
            case 'Cancelled':
                $state  = 'cancelled';
                $status = 'cancelled';
                break;

        }


        $delivery_note = (new DeliveryNote)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Delivery Note Key'},

            ], [
                'tenant_id'   => $tenant->id,
                'store_id'    => $order->store_id,
                'customer_id' => $order->customer_id,
                'order_id'    => $order->id,

                'number' => $legacy_data->{'Delivery Note ID'},


                'weight' => $legacy_data->{'Delivery Note Weight'},
                //'items'  => $legacy_data->{'Delivery Note Number Items'},


                'state'  => $state,
                'status' => $status,
                'date'   => $legacy_data->{'Delivery Note Date'},


                'data'       => $order_data,
                'created_at' => $legacy_data->{'Delivery Note Date Created'},


            ]
        );


        //enum('Ready to be Picked','Picker Assigned','Picking','Picked','Packing','Packed','Packed Done','Approved','Dispatched','Cancelled','Cancelled to Restock')


        switch ($legacy_data->{'Delivery Note State'}) {


            case 'Dispatched':

                $delivery_note->dispatched_at = $legacy_data->{'Delivery Note Date Cancelled'};

                break;
            case 'Cancelled':

                $delivery_note->cancelled_at = $legacy_data->{'Delivery Note Date Dispatched'};

                break;


        }

        if ($legacy_data->{'Delivery Note Assigned Picker Key'}) {
            /**
             * @var $picker \App\Models\HR\Employee
             */
            if ($picker = (new Employee)->firstWhere('legacy_id', $legacy_data->{'Delivery Note Assigned Picker Key'})) {

                $delivery_note->picker_id = $picker->user->id;

            }

        }

        if ($legacy_data->{'Delivery Note Assigned Packer Key'}) {
            /**
             * @var $packer \App\Models\HR\Employee
             */
            if ($packer = (new Employee)->firstWhere('legacy_id', $legacy_data->{'Delivery Note Assigned Packer Key'})) {
                $delivery_note->picker_id = $packer->user->id;
            }

        }


        $delivery_note->save();

        return $delivery_note;
    }


}
