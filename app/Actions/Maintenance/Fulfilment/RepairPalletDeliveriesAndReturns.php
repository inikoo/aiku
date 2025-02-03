<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Jan 2025 14:56:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

/** @noinspection DuplicatedCode */

namespace App\Actions\Maintenance\Fulfilment;

use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillTotals;
use App\Actions\Fulfilment\RecurringBillTransaction\CalculateRecurringBillTransactionAmounts;
use App\Actions\Fulfilment\RecurringBillTransaction\CalculateRecurringBillTransactionCurrencyExchangeRates;
use App\Actions\Fulfilment\RecurringBillTransaction\CalculateRecurringBillTransactionDiscountPercentage;
use App\Actions\Fulfilment\RecurringBillTransaction\CalculateRecurringBillTransactionTemporalQuantity;
use App\Actions\Fulfilment\RecurringBillTransaction\StoreRecurringBillTransaction;
use App\Actions\Fulfilment\RecurringBillTransaction\UpdateRecurringBillTransaction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
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
        $this->fixPalletDispatchedAt();

        $this->fixPalletDeliveryRecurringBill();
        $this->fixPalletDeliveryTransactionsRecurringBill();

        $this->palletsStartDate();
        $this->palletsEndDate();
        $this->palletsValidateStartEndDate();

        $this->fixPalletReturnRecurringBill();
        $this->fixPalletReturnTransactionsRecurringBill();
        $this->fixNonRentalRecurringBillTransactions();
        /** @var RecurringBill $recurringBill */
        foreach (RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get() as $recurringBill) {
            $transactions = $recurringBill->transactions()->get();

            foreach ($transactions as $transaction) {
                $transaction = CalculateRecurringBillTransactionDiscountPercentage::make()->action($transaction);
                $transaction = CalculateRecurringBillTransactionTemporalQuantity::run($transaction);
                $transaction = CalculateRecurringBillTransactionAmounts::run($transaction);
                CalculateRecurringBillTransactionCurrencyExchangeRates::run($transaction);
            }

            CalculateRecurringBillTotals::make()->action($recurringBill);
        }
    }

    public function palletsValidateStartEndDate(): void
    {
        /** @var RecurringBill $recurringBill */
        foreach (RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get() as $recurringBill) {
            $transactions = $recurringBill->transactions()->where('item_type', 'Pallet')->get();

            /** @var RecurringBillTransaction $transaction */
            foreach ($transactions as $transaction) {
                $currentStartDay = $transaction->start_date->startOfDay();
                $currentEndDay   = $transaction->end_date ? $transaction->end_date->startOfDay() : null;

                if ($currentEndDay and $currentEndDay->isBefore($currentStartDay)) {
                    print 'Pallet: '.$transaction->id.' end date is before start date '.$currentStartDay->format('Y-m-d').' -> '.$currentEndDay->format('Y-m-d')."\n";
                }
            }
        }
    }

    public function fixPalletDispatchedAt(): void
    {
        $pallets = Pallet::where('state', PalletStateEnum::DISPATCHED)->get();
        /** @var Pallet $pallet */
        foreach ($pallets as $pallet) {
            $palletReturn = $pallet->palletReturn;
            if (!$palletReturn and !$pallet->dispatched_at) {
                //  print 'Pallet: '.$pallet->id.' dont have pallet return!!'."\n";
                continue;
            }

            if (!$pallet->dispatched_at or $palletReturn->dispatched_at or $palletReturn->dispatched_at->ne($pallet->dispatched_at)) {
                $palletDate = 'no date';
                if ($pallet->dispatched_at) {
                    $palletDate = $pallet->dispatched_at->format('Y-m-d');
                }

                $palledReturnDate = 'no date';
                if ($palletReturn->dispatched_at) {
                    $palledReturnDate = $palletReturn->dispatched_at->format('Y-m-d');
                }

                if ($palletDate == 'no date' and $palledReturnDate != 'no date') {
                    print 'Pallet: Dispatch mismatch '.$pallet->id.' '.$palletDate.' -> '.$palledReturnDate."\n";


                    $pallet->update(['dispatched_at' => $palletReturn->dispatched_at]);
                }
                if ($palletDate != 'no date' and $palledReturnDate == 'no date') {
                    print 'Pallet: Big error Dispatch mismatch '.$pallet->id.' '.$palletDate.' -> '.$palledReturnDate."\n";
                } elseif ($palletReturn->dispatched_at->ne($pallet->dispatched_at)) {
                    print 'Pallet: Dispatch mismatch '.$pallet->id.' '.$palletDate.' -> '.$palledReturnDate."\n";
                    // $palletReturn->update(['dispatched_at' => $pallet->dispatched_at]);

                }
            }
        }
    }

    public function palletsStartDate(): void
    {
        /** @var RecurringBill $recurringBill */
        foreach (RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get() as $recurringBill) {
            $transactions = $recurringBill->transactions()->where('item_type', 'Pallet')->get();

            /** @var RecurringBillTransaction $transaction */
            foreach ($transactions as $transaction) {
                $currentStartDay   = $transaction->start_date->startOfDay();
                $originalStartDate = $currentStartDay;
                /** @var Pallet $pallet */
                $pallet = $transaction->item;
                if ($pallet->received_at and $pallet->received_at->startOfDay()->isAfter($currentStartDay)) {
                    $currentStartDay = $pallet->received_at->startOfDay();
                }

                if ($originalStartDate->ne($currentStartDay)) {
                    print 'PSD: '.$transaction->id.'  '.$originalStartDate->format('Y-m-d').' -> '.$currentStartDay->format('Y-m-d')."\n";
                    $transaction->update(['start_date' => $currentStartDay]);
                }
            }
        }
    }

    public function palletsEndDate(): void
    {
        /** @var RecurringBill $recurringBill */
        foreach (RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get() as $recurringBill) {
            $transactions = $recurringBill->transactions()->where('item_type', 'Pallet')->get();

            /** @var RecurringBillTransaction $transaction */
            foreach ($transactions as $transaction) {
                /** @var Pallet $pallet */
                $pallet = $transaction->item;
                if ($pallet->dispatched_at) {
                    if (!$transaction->end_date) {
                        print 'PED: '.$transaction->id.' add end day '.$pallet->dispatched_at->format('Y-m-d')."\n";
                        $transaction->update(['end_date' => $pallet->dispatched_at]);
                    } else {
                        $currentEndDay     = $transaction->end_date->startOfDay();
                        $originalStartDate = $currentEndDay;
                        if ($pallet->dispatched_at->startOfDay()->ne($currentEndDay)) {
                            $currentEndDay = $pallet->dispatched_at->startOfDay();
                        }

                        if ($originalStartDate->ne($currentEndDay)) {
                            print 'PED: '.$transaction->id.' '.$originalStartDate->format('Y-m-d').' -> '.$currentEndDay->format('Y-m-d')."\n";
                        }
                    }
                } elseif ($transaction->end_date) {
                    print 'PED: '.$transaction->id.' remove end day '.$transaction->end_date->format('Y-m-d')."\n";
                    $transaction->update(['end_date' => null]);
                }
            }
        }
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
