<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 23:16:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Ordering\Transaction\StoreTransactionFromAdjustment;
use App\Actions\Ordering\Transaction\StoreTransactionFromCharge;
use App\Actions\Ordering\Transaction\StoreTransactionFromShipping;
use App\Actions\Ordering\Transaction\UpdateTransaction;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraNoProductTransactions
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id, Order $order): ?Transaction
    {
        if ($transactionData = $organisationSource->fetchNoProductTransaction(id: $source_id, order: $order)) {


            $transactionData['transaction']['state'] =$order->state->value;
            $transactionData['transaction']['status']=$order->status->value;



            if ($transactionData['type'] == 'Adjustment') {
                $transaction = $this->processAdjustmentTransaction($order, $transactionData);
            } elseif ($transactionData['type'] == 'Shipping') {
                $transaction = $this->processShippingTransaction($order, $transactionData);

            } elseif ($transactionData['type'] == 'Charges') {
                $transaction = $this->processChargeTransaction($order, $transactionData);

            } else {
                dd($transactionData['type']);

            }


            return $transaction;
        }


        return null;
    }

    public function processAdjustmentTransaction(Order $order, array $transactionData): Transaction
    {
        if ($transaction = Transaction::where('alt_source_id', $transactionData['transaction']['alt_source_id'])->first()) {
            $transaction = UpdateTransaction::make()->action(
                transaction: $transaction,
                modelData: $transactionData['transaction'],
            );
        } else {
            $transaction = StoreTransactionFromAdjustment::make()->action(
                order: $order,
                adjustment: $transactionData['adjustment'],
                modelData: $transactionData['transaction'],
                strict: false
            );

            $sourceData = explode(':', $transaction->alt_source_id);
            DB::connection('aurora')->table('Order Transaction Fact')
                ->where('Order Transaction Fact Key', $sourceData[1])
                ->update(['aiku_id' => $transaction->id]);
        }

        return $transaction;
    }

    public function processShippingTransaction(Order $order, array $transactionData): Transaction
    {
        if ($transaction = Transaction::where('alt_source_id', $transactionData['transaction']['alt_source_id'])->first()) {
            $transaction = UpdateTransaction::make()->action(
                transaction: $transaction,
                modelData: $transactionData['transaction'],
            );
        } else {
            $transaction = StoreTransactionFromShipping::make()->action(
                order: $order,
                modelData: $transactionData['transaction'],
                strict: false
            );

            $sourceData = explode(':', $transaction->alt_source_id);
            DB::connection('aurora')->table('Order Transaction Fact')
                ->where('Order Transaction Fact Key', $sourceData[1])
                ->update(['aiku_id' => $transaction->id]);
        }

        return $transaction;
    }

    public function processChargeTransaction(Order $order, array $transactionData): Transaction
    {
        if ($transaction = Transaction::where('alt_source_id', $transactionData['transaction']['alt_source_id'])->first()) {
            $transaction = UpdateTransaction::make()->action(
                transaction: $transaction,
                modelData: $transactionData['transaction'],
            );
        } else {
            $transaction = StoreTransactionFromCharge::make()->action(
                order: $order,
                modelData: $transactionData['transaction'],
                strict: false
            );

            $sourceData = explode(':', $transaction->alt_source_id);
            DB::connection('aurora')->table('Order Transaction Fact')
                ->where('Order Transaction Fact Key', $sourceData[1])
                ->update(['aiku_id' => $transaction->id]);
        }

        return $transaction;
    }

}
