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

        $today       = Carbon::now()->setTimezone('UTC');
        $startDate       = Carbon::parse($recurringBillTransaction->start_date)->setTimezone('UTC');
        $daysDifference = ceil($startDate->diffInDays($today));

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
