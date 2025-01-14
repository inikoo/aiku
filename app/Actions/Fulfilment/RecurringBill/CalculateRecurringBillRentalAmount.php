<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 21:09:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\RecurringBillTransaction\CalculateRecurringBillTransactionTemporalQuantity;
use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;

class CalculateRecurringBillRentalAmount extends OrgAction
{
    public function handle(RecurringBill $recurringBill): RecurringBill
    {
        /** @var RecurringBillTransaction $transactions */
        $transactions = $recurringBill->transactions()->where('item_type', 'Pallet')->get();

        foreach ($transactions as $transaction) {
            CalculateRecurringBillTransactionTemporalQuantity::run($transaction);
        }

        CalculateRecurringBillTotals::dispatch($recurringBill)->delay($this->hydratorsDelay);

        return $recurringBill;
    }

    public function action(RecurringBill $recurringBill, int $hydratorsDelay = 0): RecurringBill
    {
        $this->asAction = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($recurringBill->fulfilment, []);
        return $this->handle($recurringBill);
    }



}
