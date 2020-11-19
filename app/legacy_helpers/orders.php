<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:27:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Sales\Order;
use App\Models\Sales\OrderTransaction;
use App\Models\Sales\TaxBand;
use App\Models\Stores\ProductHistoricVariation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

if (!function_exists('relocate_order_transactions')) {
    function relocate_order_transactions($order) {


        $toDelete = [
            'ProductHistoricVariation' => $order->transactions->whereIn('transaction_type', 'Product')->pluck('id', 'transaction_id')->all(),
            'ShippingZone'             => $order->transactions->whereIn('transaction_type', 'ShippingZone')->pluck('id', 'transaction_id')->all(),
            'Charge'                   => $order->transactions->whereIn('transaction_type', 'Charge')->pluck('id', 'transaction_id')->all()
        ];


        $otf_table         = ' `Order Transaction Fact` ';
        $otf_table_where   = ' `Order Key` ';
        $onptf_table       = ' `Order No Product Transaction Fact` ';
        $onptf_table_where = ' `Order Key` ';


        foreach (DB::connection('legacy')->select("select * from  $otf_table where  $otf_table_where=?", [$order->legacy_id]) as $otf_data) {

            $orderTransaction = (new OrderTransaction())->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'ProductHistoricVariation')->first();
            if ($orderTransaction) {

                unset($toDelete['ProductHistoricVariation'][$orderTransaction->transaction_id]);

                $orderTransaction->fill(
                    [
                        'quantity'  => $otf_data->{'Order Quantity'},
                        'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                        'net'       => $otf_data->{'Order Transaction Amount'},

                        'data' => []
                    ]
                );
                $orderTransaction->save();
            } else {

                $product_historic_variant = (new ProductHistoricVariation())->firstWhere('legacy_id', $otf_data->{'Product Key'});

                unset($toDelete['ProductHistoricVariation'][$product_historic_variant->id]);


                $orderTransactions = new OrderTransaction(
                    [

                        'order_id'  => $order->id,
                        'tenant_id' => $order->tenant_id,
                        'quantity'  => $otf_data->{'Order Quantity'},
                        'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                        'net'       => $otf_data->{'Order Transaction Amount'},
                        'legacy_id' => $otf_data->{'Order Transaction Fact Key'},
                        'data'      => []
                    ]
                );
                $product_historic_variant->orderTransactions()->save($orderTransactions);
            }


        }


        foreach (DB::connection('legacy')->select("select * from  $onptf_table where  $onptf_table_where=?", [$order->legacy_id]) as $onptf_data) {


            $transaction_data = get_legacy_transaction_data($order->store_id, $onptf_data);

            $orderTransaction = (new OrderTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first();
            if ($orderTransaction) {

                unset($toDelete[$transaction_data['type']][$orderTransaction->transaction_id]);

                $orderTransaction->fill(
                    [
                        'quantity'    => 1,
                        'discounts'   => $onptf_data->{'Transaction Total Discount Amount'},
                        'net'         => $onptf_data->{'Transaction Net Amount'},
                        'tax_band_id' => $transaction_data['tax_band_id'],
                        'data'        => []
                    ]
                );
                $orderTransaction->save();
            } else {

                unset($toDelete[$transaction_data['type']][$transaction_data['id']]);


                $orderTransaction = new OrderTransaction(
                    [

                        'order_id'         => $order->id,
                        'tenant_id'        => $order->tenant_id,
                        'transaction_type' => $transaction_data['type'],
                        'transaction_id'   => $transaction_data['id'],
                        'quantity'         => 1,
                        'discounts'        => $onptf_data->{'Transaction Total Discount Amount'},
                        'net'              => $onptf_data->{'Transaction Net Amount'},
                        'tax_band_id'      => $transaction_data['tax_band_id'],

                        'legacy_id' => $onptf_data->{'Order No Product Transaction Fact Key'},
                        'data'      => []
                    ]
                );
                $orderTransaction->save();
            }


        }


        OrderTransaction::destroy(Arr::flatten($toDelete));


    }
}

if (!function_exists('get_legacy_transaction_data')) {
    function get_legacy_transaction_data($store_id, $onptf_data) {


        switch ($onptf_data->{'Transaction Type'}) {
            case 'Shipping':
                $transaction_type = 'ShippingZone';
                $transaction_id   = get_legacy_shipping_transaction_id($onptf_data->{'Transaction Type Key'});
                break;
            case 'Charges':
                $transaction_type = 'Charge';
                $transaction_id   = get_legacy_charges_transaction_id($onptf_data->{'Transaction Type Key'});
                break;
            case 'Insurance':
                $transaction_type = 'Charge';
                $transaction_id   = get_legacy_type_charges_transaction_id('insurance', $store_id);
                break;
            case 'Premium':
                $transaction_type = 'Charge';
                $transaction_id   = get_legacy_type_charges_transaction_id('premium', $store_id);
                break;
            case 'Adjust':
                $transaction_type = 'Adjust';
                $transaction_id   = get_legacy_type_adjusts_transaction_id('legacy', $store_id);
                break;
            case 'Credit':
                $transaction_type = 'Adjust';
                $transaction_id   = get_legacy_type_adjusts_transaction_id('credit', $store_id);
                break;
            case 'Refund':
                $transaction_type = 'Adjust';
                $transaction_id   = get_legacy_type_adjusts_transaction_id('refund', $store_id);
                break;
            default:
                print "\n ".$onptf_data->{'Order No Product Transaction Fact Key'}."  transaction type : ".$onptf_data->{'Transaction Type'}."\n";

                exit();
        }
        $tax_band_id = null;
        $taxBand     = (new TaxBand)->firstwhere('code', strtolower($onptf_data->{'Tax Category Code'}));
        if ($taxBand) {
            $tax_band_id = $taxBand->id;
        } else {

            if ($transaction_type != 'Adjust') {
                print "\n ".$onptf_data->{'Order No Product Transaction Fact Key'}."   tax_code: ".$onptf_data->{'Tax Category Code'}."\n";
                exit;
            }
        }


        return [
            'type'        => $transaction_type,
            'id'          => $transaction_id,
            'tax_band_id' => $tax_band_id

        ];
    }
}

if (!function_exists('relocate_order')) {
    function relocate_order($parent, $legacy_data) {
        $order_data = fill_legacy_data(
            [

            ], $legacy_data
        );

        /*
                $store    = (new Store)->firstWhere('legacy_id', $legacy_data->{'Order Store Key'});
                $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Key'});

                $customer_client_id = null;
                if ($legacy_data->{'Order Customer Client Key'}) {
                    $customer_client    = CustomerClient::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Client Key'});
                    $customer_client_id = $customer_client->id;
                }
        */

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
                'tenant_id'          => $parent->tenant_id,
                'store_id'           => $parent->store_id,
                'customer_id'        => $parent->customer_id,
                'customer_client_id' => $parent->customer_client_id,
                'number'             => $legacy_data->{'Order Public ID'},
                'payment'            => $legacy_data->{'Order Payments Amount'},
                'items_discounts'    => $legacy_data->{'Order Items Discount Amount'},
                'shipping'           => $legacy_data->{'Order Shipping Net Amount'},
                'charges'            => $legacy_data->{'Order Charges Net Amount'},
                'net'                => $legacy_data->{'Order Total Net Amount'},
                'tax'                => $legacy_data->{'Order Total Tax Amount'},
                'weight'             => $legacy_data->{'Order Estimated Weight'},
                'items'              => $legacy_data->{'Order Number Items'},
                'state'              => $state,
                'status'             => $status,
                'date'               => $legacy_data->{'Order Date'},
                'data'               => $order_data,
                'created_at'         => $legacy_data->{'Order Created Date'},


            ]
        );

        $billing_address  = process_legacy_immutable_address('Order', 'Invoice', $legacy_data);
        $delivery_address = process_legacy_immutable_address('Order', 'Delivery', $legacy_data);

        if ($billing_address->id == $delivery_address->id) {
            $order->addresses()->syncWithoutDetaching([$billing_address->id => ['scope' => 'billing_delivery']]);
        } else {
            $order->addresses()->syncWithoutDetaching([$billing_address->id => ['scope' => 'billing']]);
            $order->addresses()->syncWithoutDetaching([$delivery_address->id => ['scope' => 'delivery']]);
        }


        $order->billing_address_id  = $billing_address->id;
        $order->delivery_address_id = $delivery_address->id;
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
}


