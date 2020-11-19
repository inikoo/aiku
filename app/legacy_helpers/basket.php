<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:40:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Sales\BasketTransaction;
use App\Models\Stores\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

            $basketItem = (new BasketTransaction)->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'Product')->first();
            if ($basketItem) {


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


            $transaction_data = get_legacy_transaction_data($basket->parent->store_id, $onptf_data);

            $basketItem = (new BasketTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first();
            if ($basketItem) {

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
