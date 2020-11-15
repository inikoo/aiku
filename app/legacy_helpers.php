<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 04:35:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Helpers\Address;
use App\Models\Sales\Adjust;
use App\Models\Sales\BasketTransaction;
use App\Models\Sales\Charge;
use App\Models\Sales\Order;
use App\Models\Sales\OrderTransaction;
use App\Models\Sales\ShippingZone;
use App\Models\Sales\TaxBand;
use App\Models\Stores\Product;
use App\Models\Stores\ProductHistoricVariation;
use App\Models\Stores\Store;
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
    /**
     * @param $legacy_parent_id
     * @param $basket
     *
     * @return mixed
     * @throws \Exception
     */
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

                if ($product->id) {

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
                } else {
                    throw new Exception('Product not found: '.$otf_data->{'Product ID'});
                }

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


            $transaction_data = get_legacy_transaction_data($basket->parent->store_id,$onptf_data);


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
            'ProductHistoricVariation' => $order->transactions->whereIn('transaction_type', 'Product')->pluck('id', 'transaction_id')->all(),
            'ShippingZone'             => $order->transactions->whereIn('transaction_type', 'ShippingZone')->pluck('id', 'transaction_id')->all(),
            'Charge'                   => $order->transactions->whereIn('transaction_type', 'Charge')->pluck('id', 'transaction_id')->all()
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


            $transaction_data = get_legacy_transaction_data($order->store_id,$onptf_data);


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
    function get_legacy_transaction_data($store_id, $onptf_data) {








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
                $transaction_type = 'Charge';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {

                    /**
                     * @var $charge Charge
                     */
                    if ($charge=(new Store)->find($store_id)->charges()->firstWhere('type', 'insurance')) {
                        $transaction_id = $charge->id;
                    }

                }
                break;
            case 'Premium':
                $transaction_type = 'Charge';
                $transaction_id   = null;
                if ($onptf_data->{'Transaction Type Key'}) {
                    /**
                     * @var $charge Charge
                     */
                    if ($charge=(new Store)->find($store_id)->charges()->firstWhere('type', 'premium')) {
                        $transaction_id = $charge->id;
                    }
                }
                break;
            case 'Adjust':
                $transaction_type = 'Adjust';
                $transaction_id   = null;
                /**
                 * @var $adjust Adjust
                 */
                if ($adjust=(new Store)->find($store_id)->adjusts()->firstWhere('type', 'legacy')) {
                    $transaction_id = $adjust->id;
                }




                break;
            default:
                print "\n ".$onptf_data->{'Order No Product Transaction Fact Key'}."  transaction type : ".$onptf_data->{'Transaction Type'}."\n";

                exit();
        }
        $tax_band_id = null;
        if ($taxBand = (new TaxBand)->firstwhere('code', strtolower($onptf_data->{'Tax Category Code'}))) {
            $tax_band_id = $taxBand->id;
        } else {
            print "\n ".$onptf_data->{'Order No Product Transaction Fact Key'}."   tax_code: ".$onptf_data->{'Tax Category Code'}."\n";

            exit;
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


function process_legacy_immutable_address($object, $type, $legacy_data) {


    $_address = get_legacy_instance_address_scaffolding($object, $type, $legacy_data);

    return (new Address)->firstOrCreate(
        [
            'checksum'   => $_address->getChecksum(),
            'owner_type' => null,
            'owner_id'   => null,

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


function get_legacy_instance_address_scaffolding($object, $type, $legacy_data) {

    if ($object == 'CustomerClient') {
        $legacy_object = 'Customer Client';
    } else {
        $legacy_object = $object;
    }


    if ($type != '') {
        $type = ' '.$type;
    }

    $_address                      = new Address();
    $_address->address_line_1      = $legacy_data->{$legacy_object.$type.' Address Line 1'};
    $_address->address_line_2      = $legacy_data->{$legacy_object.$type.' Address Line 2'};
    $_address->sorting_code        = $legacy_data->{$legacy_object.$type.' Address Sorting Code'};
    $_address->postal_code         = $legacy_data->{$legacy_object.$type.' Address Postal Code'};
    $_address->locality            = $legacy_data->{$legacy_object.$type.' Address Locality'};
    $_address->dependent_locality  = $legacy_data->{$legacy_object.$type.' Address Dependent Locality'};
    $_address->administrative_area = $legacy_data->{$legacy_object.$type.' Address Administrative Area'};
    $_address->country_code        = $legacy_data->{$legacy_object.$type.' Address Country 2 Alpha Code'};

    return $_address;


}
