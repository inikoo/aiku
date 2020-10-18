<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 23:47:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


namespace App\Models\Traits;


use Illuminate\Support\Facades\DB;

trait OrderTotals {


    public function getTotals() {

        $transactions_table = 'transactions';

        if ($this->getMorphClass() == 'Basket') {
            $transactions_table = 'basket_transactions';
        }

        $itemsTotals    = DB::table($transactions_table)->select(DB::raw('sum(net) sum_net, sum(discounts) sum_discounts, count(*) num'))->where('basket_id', $this->id)->where('transaction_type', 'Product')->first();
        $shippingTotals = DB::table($transactions_table)->select(DB::raw('sum(net) sum_net, sum(discounts) sum_discounts, count(*) num'))->where('basket_id', $this->id)->where('transaction_type', 'ShippingZone')->first();
        $chargesTotals  = DB::table($transactions_table)->select(DB::raw('sum(net) sum_net, sum(discounts) sum_discounts'))->where('basket_id', $this->id)->where('transaction_type', 'Charge')->first();


        $shipping = $shippingTotals->sum_net;
        $charges  = $chargesTotals->sum_net;
        if (!$charges) {
            $charges = 0;
        }
        if ($itemsTotals->num == 0) {
            $itemsTotals->sum_net       = 0;
            $itemsTotals->sum_discounts = 0;
        }


        return [
            'items'           => $itemsTotals->num,
            'items_discounts' => $itemsTotals->sum_discounts,
            'items_net'       => $itemsTotals->sum_net,

            'shipping' => $shipping,
            'charges'  => $charges,
            'net'      => $itemsTotals->sum_net + $shipping + $charges

        ];

    }

    public function updateTotals() {

        $this->fill($this->getTotals());

        if ($this->getMorphClass() == 'Basket') {
            $this->status = $this->items > 0;
        }

        $this->save();

    }

}
