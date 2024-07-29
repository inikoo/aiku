<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 09:00:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBillTransaction;

class SetClausesInRecurringBillTransaction extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction)
    {
        $rentalAgreementClauses = $recurringBillTransaction->fulfilmentCustomer->rentalAgreementClauses;
        $percentageOff = 0;
        $found = false;
        foreach ($rentalAgreementClauses as $clause) {
            if ($clause->asset_id === $recurringBillTransaction->asset_id) {
                data_set($modelData, 'rental_agreement_clause_id', $clause->id);
                $percentageOff = $clause->percentage_off / 100;
                $found = true;
                break;
            }
        }

        if (!$found) {
            data_set($modelData, 'rental_agreement_clause_id', null);
        }

        $net = $recurringBillTransaction->net_amount;
        $net -= $net * $percentageOff;

        data_set($modelData, 'net_amount', $net);
        
        $recurringBillTransaction->update($modelData);
    }
}