<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 21:09:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBillTransaction;

class CalculateRecurringBillTransactionAmounts extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction): RecurringBillTransaction
    {
        $grossAmount = $recurringBillTransaction->unit_cost * $recurringBillTransaction->quantity * $recurringBillTransaction->temporal_quantity;


        $recurringBillTransaction->update([
            'gross_amount' => $grossAmount,
            'net_amount'   => $grossAmount * (1 - $recurringBillTransaction->discount_percentage),
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
