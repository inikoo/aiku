<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Jan 2025 14:56:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

/** @noinspection DuplicatedCode */

namespace App\Actions\Maintenance\Fulfilment;

use App\Actions\Fulfilment\RecurringBillTransaction\StoreRecurringBillTransaction;
use App\Actions\Fulfilment\RecurringBillTransaction\UpdateRecurringBillTransaction;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RepairPalletDeliveriesAndReturns
{
    use AsAction;

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->fixPalletDeliveryRecurringBill();
        $this->fixPalletDeliveryTransactionsRecurringBill();
        $this->fixPalletReturnRecurringBill();
        $this->fixPalletReturnTransactionsRecurringBill();
        $this->fixNonRentalRecurringBillTransactions();
    }

    public function fixNonRentalRecurringBillTransactions(): void
    {
        $recurringBills = RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get();

        /** @var RecurringBill $recurringBill */
        foreach ($recurringBills as $recurringBill) {
            $recurringBill->transactions->each(function ($transaction) use ($recurringBill) {
                if (($transaction->pallet_delivery_id || $transaction->pallet_return_id) and !in_array($transaction->item_type, ['Pallet', 'StoredItem'])) {
                    $fulfilmentTransaction = $transaction->fulfilmentTransaction;


                    if ($fulfilmentTransaction->quantity != $transaction->quantity) {
                        print "Fix Fulfilment Transaction Qty: $fulfilmentTransaction->id\n";
                        UpdateRecurringBillTransaction::make()->action(
                            $transaction,
                            [
                                'quantity' => $fulfilmentTransaction->quantity
                            ]
                        );

                    }
                }
            });
        }
    }

    public function fixPalletDeliveryTransactionsRecurringBill(): void
    {
        $palletDeliveries = PalletDelivery::whereNotNull('recurring_bill_id')->get();
        /** @var PalletDelivery $palletDelivery */
        foreach ($palletDeliveries as $palletDelivery) {
            $palletDelivery->transactions->each(function ($transaction) use (
                $palletDelivery
            ) {
                if (!$palletDelivery->recurringBill->transactions()->where('fulfilment_transaction_id', $transaction->id)->exists()) {
                    print "Fix Pallet Delivery Transaction CRB: $transaction->id\n";

                    StoreRecurringBillTransaction::make()->action(
                        $palletDelivery->recurringBill,
                        $transaction,
                        [
                            'start_date'                => now(),
                            'quantity'                  => $transaction->quantity,
                            'pallet_delivery_id'        => $palletDelivery->id,
                            'fulfilment_transaction_id' => $transaction->id
                        ]
                    );
                }
            });
        }
    }

    public function fixPalletReturnTransactionsRecurringBill(): void
    {
        $palletReturns = PalletReturn::whereNotNull('recurring_bill_id')->get();
        /** @var PalletReturn $palletReturn */
        foreach ($palletReturns as $palletReturn) {
            $palletReturn->transactions->each(function ($transaction) use (
                $palletReturn
            ) {
                if (!$palletReturn->recurringBill->transactions()->where('fulfilment_transaction_id', $transaction->id)->exists()) {
                    print "Fix Pallet return Transaction CRB: $transaction->id\n";

                    StoreRecurringBillTransaction::make()->action(
                        $palletReturn->recurringBill,
                        $transaction,
                        [
                            'start_date'                => now(),
                            'quantity'                  => $transaction->quantity,
                            'pallet_return_id'          => $palletReturn->id,
                            'fulfilment_transaction_id' => $transaction->id
                        ]
                    );
                }
            });
        }
    }

    public function fixPalletDeliveryRecurringBill(): void
    {
        $palletDeliveries = PalletDelivery::whereNull('recurring_bill_id')->get();
        /** @var PalletDelivery $palletDelivery */
        foreach ($palletDeliveries as $palletDelivery) {
            if ($receivedDate = $palletDelivery->received_at and $palletDelivery->fulfilmentCustomer->currentRecurringBill) {
                $currentRecurringBill = $palletDelivery->fulfilmentCustomer->currentRecurringBill;
                if ($receivedDate->isAfter($currentRecurringBill->start_date)) {
                    print "Fix Pallet Delivery CRB: $palletDelivery->id\n";
                    $palletDelivery->update(['recurring_bill_id' => $currentRecurringBill->id]);
                }
            }
        }
    }

    public function fixPalletReturnRecurringBill(): void
    {
        $palletReturns = PalletReturn::whereNull('recurring_bill_id')->get();
        /** @var PalletReturn $palletReturn */
        foreach ($palletReturns as $palletReturn) {
            if ($receivedDate = $palletReturn->dispatched_at and $palletReturn->fulfilmentCustomer->currentRecurringBill) {
                $currentRecurringBill = $palletReturn->fulfilmentCustomer->currentRecurringBill;
                if ($receivedDate->isAfter($currentRecurringBill->start_date)) {
                    print "Fix Pallet Return CRB: $palletReturn->id\n";
                    $palletReturn->update(['recurring_bill_id' => $currentRecurringBill->id]);
                }
            }
        }
    }

    public function getCommandSignature(): string
    {
        return 'maintenance:repair_pallet_deliveries_and_returns';
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }

}
