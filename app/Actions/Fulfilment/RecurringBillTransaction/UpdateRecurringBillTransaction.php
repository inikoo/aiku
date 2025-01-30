<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 Jan 2025 00:37:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillTotals;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\RecurringBillTransaction;
use Lorisleiva\Actions\ActionRequest;

class UpdateRecurringBillTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(RecurringBillTransaction $recurringBillTransaction, array $modelData): RecurringBillTransaction
    {
        $recurringBillTransaction = $this->update($recurringBillTransaction, $modelData, ['data']);
        $recurringBillTransaction = CalculateRecurringBillTransactionDiscountPercentage::make()->action($recurringBillTransaction, $this->hydratorsDelay);
        $recurringBillTransaction = CalculateRecurringBillTransactionTemporalQuantity::make()->action($recurringBillTransaction);
        $recurringBillTransaction = CalculateRecurringBillTransactionAmounts::make()->action($recurringBillTransaction);
        $recurringBillTransaction = CalculateRecurringBillTransactionCurrencyExchangeRates::make()->action($recurringBillTransaction);

        CalculateRecurringBillTotals::run($recurringBillTransaction->recurringBill);


        return $recurringBillTransaction;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }



    public function asController(RecurringBillTransaction $recurringBillTransaction, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($recurringBillTransaction->fulfilment, $actionRequest);

        $this->handle($recurringBillTransaction, $this->validatedData);
    }

    public function action(RecurringBillTransaction $recurringBillTransaction, array $modelData): RecurringBillTransaction
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($recurringBillTransaction->fulfilment, $modelData);

        return $this->handle($recurringBillTransaction, $this->validatedData);
    }

}
