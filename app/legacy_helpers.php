<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 04:35:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Helpers\Address;
use App\Models\Sales\BasketTransaction;
use App\Models\Sales\Charge;
use App\Models\Sales\OrderTransaction;
use App\Models\Sales\ShippingZone;
use App\Models\Sales\TaxBand;
use App\Models\Stores\Product;
use App\Models\Stores\ProductHistoricVariation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

if (!function_exists('legacy_process_addresses')) {
    function legacy_process_addresses($customer, $billing_address, $delivery_address) {
        $oldAddressIds = $customer->addresses->pluck('id')->all();

        if ($billing_address->id == $delivery_address->id) {
            $customer->addresses()->sync([$billing_address->id => ['scope' => 'billing_delivery']]);
        } else {
            $customer->addresses()->sync(
                [
                    $billing_address->id  => ['scope' => 'billing'],
                    $delivery_address->id => ['scope' => 'delivery']
                ]
            );

        }


        $customer->billing_address_id  = $billing_address->id;
        $customer->country_id          = $billing_address->country_id;
        $customer->delivery_address_id = $delivery_address->id;
        $customer->save();

        $customer = $customer->fresh();

        $addressIds = $customer->addresses->pluck('id')->all();


        foreach (array_diff($oldAddressIds, $addressIds) as $addressToDelete) {
            if ($address = (new Address)->find($addressToDelete)) {
                $address->deleteIfOrphan();
            }

        }

        return $customer;

    }
}
if (!function_exists('legacy_get_address')) {

    function legacy_get_address($object, $object_key, $address_data) {


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

if (!function_exists('fill_legacy_data')) {
    function fill_legacy_data($fields, $legacy_data, $modifier = false) {

        $data = [];
        foreach ($fields as $key => $legacy_key) {
            if (!empty($legacy_data->{$legacy_key})) {
                if ($modifier == 'strtolower') {
                    $value = strtolower($legacy_data->{$legacy_key});
                } elseif ($modifier == 'jsonDecode') {
                    $value = json_decode($legacy_data->{$legacy_key}, true);
                } else {
                    $value = $legacy_data->{$legacy_key};
                }
                Arr::set($data, $key, $value);
            }
        }

        return $data;
    }

}


if (!function_exists('relocate_historic_products')) {
    function relocate_historic_products($legacy_data, $product_id) {


        $historic_product_data = fill_legacy_data(
            [
                'code' => 'Product History Code',
                'name' => 'Product History Name'
            ], $legacy_data
        );


        $units = $legacy_data->{'Product History Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        if ($legacy_data->{'Product History Valid From'} == '0000-00-00 00:00:00') {
            $date = null;
        } else {
            $date = $legacy_data->{'Product History Valid From'};
        }

        return (new ProductHistoricVariation)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Product Key'},
            ], [

                'product_id' => $product_id,
                'unit_price' => $legacy_data->{'Product History Price'} / $units,
                'units'      => $units,
                'data'       => $historic_product_data,
                'date'       => $date,
            ]
        );
    }
}
if (!function_exists('relocate_basket')) {
    function relocate_basket($legacy_parent_id, $basket) {


        if ($basket->parent_type == 'Customer') {
            $legacy_column_name = 'Order Customer Key';
        } else {
            $legacy_column_name = 'Order Customer Client Key';

        }


        $toDelete = [
            'Product'      => $basket->transactions->whereIn('transaction_type', 'Product')->pluck('id', 'transaction_id')->all(),
            'ShippingZone' => $basket->transactions->whereIn('transaction_type', 'ShippingZone')->pluck('id', 'transaction_id')->all(),
            'Charge'       => $basket->transactions->whereIn('transaction_type', 'Charge')->pluck('id', 'transaction_id')->all()
        ];


        $sql = " * from  `Order Transaction Fact` OTF  left join `Order Dimension` O on (O.`Order Key`=OTF.`Order Key`)   where `$legacy_column_name`=? and `Order State`=?";
        foreach (
            DB::connection('legacy')->select(
                'select '.$sql, [
                                  $legacy_parent_id,
                                  'InBasket'
                              ]
            ) as $otf_data
        ) {


            if ($basketItem = (new BasketTransaction)->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'Product')->first()) {


                unset($toDelete['Product'][$basketItem->transaction_id]);


                $basketItem->fill(
                    [
                        'quantity'  => $otf_data->{'Order Quantity'},
                        'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                        'net'       => $otf_data->{'Order Transaction Amount'},
                        'data'      => []
                    ]
                );
                $basketItem->save();
                $currentTransactions[] = $basketItem->id;
            } else {

                $product = (new Product())->firstWhere('legacy_id', $otf_data->{'Product ID'});

                unset($toDelete['Product'][$product->id]);


                $basketItems = new BasketTransaction(
                    [
                        'basket_id' => $basket->id,

                        'tenant_id' => $product->tenant_id,
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
                                  $legacy_parent_id,
                                  'InBasket'
                              ]
            ) as $onptf_data
        ) {


            $transaction_data = get_legacy_transaction_data($onptf_data);


            if ($basketItem = (new BasketTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first()) {

                unset($toDelete[$transaction_data['type']][$basketItem->transaction_id]);

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

                unset($toDelete[$transaction_data['type']][$transaction_data['id']]);

                $basketItem = new BasketTransaction(
                    [

                        'basket_id'        => $basket->id,
                        'tenant_id'        => $basket->tenant_id,
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


        BasketTransaction::destroy(Arr::flatten($toDelete));


        return $basket;

    }
}

if (!function_exists('relocate_order_transactions')) {
    function relocate_order_transactions($order) {


        $toDelete = [
            'ProductHistoricVariation'      => $order->transactions->whereIn('transaction_type', 'Product')->pluck('id', 'transaction_id')->all(),
            'ShippingZone' => $order->transactions->whereIn('transaction_type', 'ShippingZone')->pluck('id', 'transaction_id')->all(),
            'Charge'       => $order->transactions->whereIn('transaction_type', 'Charge')->pluck('id', 'transaction_id')->all()
        ];


        $otf_table         = ' `Order Transaction Fact` ';
        $otf_table_where   = ' `Order Key` ';
        $onptf_table       = ' `Order No Product Transaction Fact` ';
        $onptf_table_where = ' `Order Key` ';


        foreach (DB::connection('legacy')->select("select * from  $otf_table where  $otf_table_where=?", [$order->legacy_id]) as $otf_data) {


            if ($orderTransaction = (new OrderTransaction())->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'ProductHistoricVariation')->first()) {

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


            $transaction_data = get_legacy_transaction_data($onptf_data);


            if ($orderTransaction = (new OrderTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first()) {

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
    function get_legacy_transaction_data($onptf_data) {

        switch ($onptf_data->{'Transaction Type'}) {
            case 'Shipping':
                $transaction_type = 'ShippingZone';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    if ($shipping_zone = (new ShippingZone())->firstWhere('legacy_id', $onptf_data->{'Transaction Type Key'})) {
                        $transaction_id = $shipping_zone->id;
                    }

                }
                break;
            case 'Charges':
                $transaction_type = 'Charge';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    if ($charge = (new Charge())->firstWhere('legacy_id', $onptf_data->{'Transaction Type Key'})) {
                        $transaction_id = $charge->id;
                    }

                }
                break;
            case 'Insurance':
                $transaction_type = 'Insurance';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    if ($charge = (new Charge())->firstWhere('type', 'insurance')) {
                        $transaction_id = $charge->id;
                    }

                }
                break;
            default:
                print_r($onptf_data);
                exit();
        }
        $tax_band_id = null;
        if ($taxBand = (new TaxBand)->firstwhere('code', strtolower($onptf_data->{'Tax Category Code'}))) {
            $tax_band_id = $taxBand->id;
        } else {
            print_r($onptf_data);
            exit;
        }


        return [
            'type'        => $transaction_type,
            'id'          => $transaction_id,
            'tax_band_id' => $tax_band_id

        ];
    }
}
