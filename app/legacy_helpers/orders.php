<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:27:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Sales\Order;
use App\Models\Sales\OrderTransaction;
use App\Models\Stores\ProductHistoricVariation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


function relocate_order_transactions($order) {

    $toDelete = get_legacy_to_delete_transactions($order);

    $sql = "* from `Order Transaction Fact` where `Order Key`=?";
    foreach (DB::connection('legacy')->select("select $sql", [$order->legacy_id]) as $otf_data) {

        $tax_band_id = get_legacy_tax_band('ProductHistoricVariation', strtolower($otf_data->{'Transaction Tax Code'}));

        $orderTransaction = (new OrderTransaction())->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'ProductHistoricVariation')->first();
        if ($orderTransaction) {

            unset($toDelete['ProductHistoricVariation'][$orderTransaction->transaction_id]);

            $orderTransaction->fill(
                [
                    'quantity'    => $otf_data->{'Order Quantity'},
                    'discounts'   => $otf_data->{'Order Transaction Total Discount Amount'},
                    'net'         => $otf_data->{'Order Transaction Amount'},
                    'tax_band_id' => $tax_band_id,
                    'data'        => []
                ]
            );
            $orderTransaction->save();
        } else {

            $product_historic_variant = (new ProductHistoricVariation())->firstWhere('legacy_id', $otf_data->{'Product Key'});

            unset($toDelete['ProductHistoricVariation'][$product_historic_variant->id]);

            $orderTransactions = new OrderTransaction(
                [
                    'order_id'     => $order->id,
                    'tenant_id'    => $order->tenant_id,
                    'quantity'     => $otf_data->{'Order Quantity'},
                    'discounts'    => $otf_data->{'Order Transaction Total Discount Amount'},
                    'net'          => $otf_data->{'Order Transaction Amount'},
                    'legacy_id'    => $otf_data->{'Order Transaction Fact Key'},
                    'legacy_scope' => 'otf',
                    'tax_band_id'  => $tax_band_id,
                    'data'         => []
                ]
            );
            $product_historic_variant->orderTransactions()->save($orderTransactions);
        }


    }

    $sql = "* from `Order No Product Transaction Fact` where  `Order Key` =?";
    foreach (DB::connection('legacy')->select("select $sql", [$order->legacy_id]) as $onptf_data) {

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
                    'legacy_scope'     => 'onptf',
                    'legacy_id'        => $onptf_data->{'Order No Product Transaction Fact Key'},
                    'data'             => []
                ]
            );
            $orderTransaction->save();
        }
    }

    OrderTransaction::destroy(Arr::flatten($toDelete));

}


function get_order_legacy_state($legacy_state) {
    $status = 'Processing';
    $state  = null;
    switch ($legacy_state) {
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

    return [
        $state,
        $status
    ];
}

function relocate_order($parent, $legacy_data) {
    $order_data = fill_legacy_data(
        [
            'customer.mame'    => 'Order Customer Name',
            'customer.contact' => 'Order Customer Contact Name',
        ], $legacy_data
    );

    if ($parent->customer_client_id) {
        $order_data['customer_client_id'] = $parent->customer_client_id;
    }

    list($state, $status) = get_order_legacy_state($legacy_data->{'Order State'});


    $order = (new Order)->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Order Key'},

        ], [
            'tenant_id'       => $parent->tenant_id,
            'customer_id'     => $parent->customer_id,
            'number'          => $legacy_data->{'Order Public ID'},
            'payment'         => $legacy_data->{'Order Payments Amount'},
            'items_discounts' => $legacy_data->{'Order Items Discount Amount'},
            'shipping'        => $legacy_data->{'Order Shipping Net Amount'},
            'charges'         => $legacy_data->{'Order Charges Net Amount'},
            'net'             => $legacy_data->{'Order Total Net Amount'},
            'tax'             => $legacy_data->{'Order Total Tax Amount'},
            'weight'          => $legacy_data->{'Order Estimated Weight'},
            'items'           => $legacy_data->{'Order Number Items'},
            'state'           => $state,
            'status'          => $status,
            'date'            => $legacy_data->{'Order Date'},
            'data'            => $order_data,
            'created_at'      => $legacy_data->{'Order Created Date'},


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


        // case 'PackedDone':
        //     break;


        case 'InProcess':
            $order->submitted_at = $legacy_data->{'Order Submitted by Customer Date'};
            break;
        case 'InWarehouse':
            $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
            $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};
            break;
        case 'Approved':
            $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};
            $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
            $order->invoiced_at   = $legacy_data->{'Order Invoiced Date'};
            break;
        case 'Dispatched':
            $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};
            $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
            $order->invoiced_at   = $legacy_data->{'Order Invoiced Date'};
            $order->dispatched_at = $legacy_data->{'Order Dispatched Date'};
            break;
        case 'Cancelled':
            $order->submitted_at  = $legacy_data->{'Order Submitted by Customer Date'};
            $order->warehoused_at = $legacy_data->{'Order Send to Warehouse Date'};
            $order->cancelled_at  = $legacy_data->{'Order Cancelled Date'};
            break;
    }

    $order->save();

    if ($parent->customer_client_id) {
        $parent->orders()->syncWithoutDetaching([$order->id]);
    }

    relocate_order_transactions($order);

    return $order;
}



