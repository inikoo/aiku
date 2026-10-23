<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Oct 2025 14:00:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction\Traits;

use App\Actions\Ordering\Transaction\DestroyTransaction;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\Ordering\Transaction\UpdateTransaction;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use App\Models\Billables\Charge;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Reusable helpers to create or update a charge transaction on an order.
 */
trait WithChargeTransactions
{
    /**
     * Create a transaction for a charge on the given order.
     */
    private function storeChargeTransaction(Order $order, Charge $charge, $chargeAmount): Transaction
    {
        return StoreTransaction::run(
            $order,
            $charge->historicAsset,
            [
                'quantity_ordered' => 1,
                'gross_amount'     => $chargeAmount,
                'net_amount'       => $chargeAmount,
            ],
            false
        );
    }

    /**
     * Update an existing transaction with the given charge/amount details.
     */
    private function updateChargeTransaction(Transaction $transaction, Charge $charge, $chargeAmount): Transaction
    {
        $grossAmount    = (float) ($chargeAmount ?? 0);
        $staffSetFactor = data_get($transaction->offers_data, 'o.t') === 'percentage_off'
            ? (float) data_get($transaction->offers_data, 'o.pf', 1)
            : 1.0;

        return UpdateTransaction::run(
            $transaction,
            [
                'model_id'          => $charge->id,
                'asset_id'          => $charge->asset_id,
                'historic_asset_id' => $charge->historicAsset->id,
                'gross_amount'      => $grossAmount,
                'net_amount'        => round($grossAmount - discountAmountOffGross($grossAmount, $staffSetFactor), 2),
            ],
            false
        );
    }

    /**
     * Apply or remove the shop's active charge of the given type, leaving at most one charge
     * transaction of that type on the order. The order row is locked so concurrent requests
     * cannot each add the charge.
     */
    private function syncOrderOptionCharge(Order $order, ChargeTypeEnum $chargeType, bool $chargeApplies): void
    {
        $charge = $order->shop->charges()->where('type', $chargeType)->where('state', ChargeStateEnum::ACTIVE)->first();
        if (!$charge) {
            return;
        }

        DB::transaction(function () use ($order, $charge, $chargeType, $chargeApplies) {
            Order::whereKey($order->id)->lockForUpdate()->first();

            $chargeTransactions = Transaction::where('transactions.order_id', $order->id)
                ->where('transactions.model_type', 'Charge')
                ->join('charges', 'transactions.model_id', '=', 'charges.id')
                ->where('charges.type', $chargeType->value)
                ->orderBy('transactions.id')
                ->select('transactions.*')
                ->get();

            $chargeTransactionToKeep = $chargeApplies ? $chargeTransactions->shift() : null;

            foreach ($chargeTransactions as $chargeTransaction) {
                DestroyTransaction::run($chargeTransaction);
            }

            if (!$chargeApplies) {
                return;
            }

            $chargeAmount = Arr::get($charge->settings, 'amount');
            if ($chargeTransactionToKeep) {
                $this->updateChargeTransaction($chargeTransactionToKeep, $charge, $chargeAmount);
            } else {
                $this->storeChargeTransaction($order, $charge, $chargeAmount);
            }
        });
    }
}
