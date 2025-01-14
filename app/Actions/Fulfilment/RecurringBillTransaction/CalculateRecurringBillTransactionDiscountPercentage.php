<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 09:00:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementCauseStateEnum;
use App\Models\Fulfilment\RecurringBillTransaction;

class CalculateRecurringBillTransactionDiscountPercentage extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction): RecurringBillTransaction
    {

        $rentalAgreementClause = $recurringBillTransaction->fulfilmentCustomer->rentalAgreementClauses()
                                    ->where('state', RentalAgreementCauseStateEnum::ACTIVE)
            ->where('asset_id', $recurringBillTransaction->asset_id)
            ->first();


        $percentageOff = 0;
        $rentalAgreementClauseID = null;
        if ($rentalAgreementClause) {
            $rentalAgreementClauseID = $rentalAgreementClause->id;
            $percentageOff = $rentalAgreementClause->percentage_off / 100;
            data_set($modelData, 'discount_percentage', $percentageOff);
            data_set($modelData, 'rental_agreement_clause_id', null);
        }
        data_set($modelData, 'discount_percentage', $percentageOff);
        data_set($modelData, 'rental_agreement_clause_id', $rentalAgreementClauseID);

        $recurringBillTransaction->update($modelData);

        return $recurringBillTransaction;
    }

    public function action(RecurringBillTransaction $recurringBillTransaction, int $hydratorsDelay = 0): RecurringBillTransaction
    {
        $this->asAction = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($recurringBillTransaction->fulfilment, []);
        return $this->handle($recurringBillTransaction);
    }
}
