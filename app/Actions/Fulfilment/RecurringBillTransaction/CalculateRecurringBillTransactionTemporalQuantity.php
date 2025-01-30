<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 21:09:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBillTransaction;
use Carbon\Carbon;

class CalculateRecurringBillTransactionTemporalQuantity extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction): RecurringBillTransaction
    {
        if (!in_array($recurringBillTransaction->item_type, ['Pallet', 'StoredItem', 'Space'])) {
            return $recurringBillTransaction;
        }

        $today   = Carbon::now()->setTimezone('UTC')->startOfDay();
        $endDate = $today;
        if ($recurringBillTransaction->end_date) {
            if (Carbon::parse($recurringBillTransaction->end_date)->setTimezone('UTC')->startOfDay()->isBefore($today)) {
                $endDate = $recurringBillTransaction->end_date;
            }
        }

        $startDate      = Carbon::parse($recurringBillTransaction->start_date)->setTimezone('UTC')->startOfDay();
        $daysDifference = floor($startDate->diffInDays($endDate) + 1);



        $recurringBillTransaction->update([
            'temporal_quantity' => $daysDifference,
        ]);

        return $recurringBillTransaction;
    }

    public function action(RecurringBillTransaction $recurringBillTransaction, int $hydratorsDelay = 0): RecurringBillTransaction
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($recurringBillTransaction->fulfilment, []);

        return $this->handle($recurringBillTransaction);
    }

}
