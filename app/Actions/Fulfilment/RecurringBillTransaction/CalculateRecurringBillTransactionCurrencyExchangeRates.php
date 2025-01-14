<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 23:51:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBillTransaction;

class CalculateRecurringBillTransactionCurrencyExchangeRates extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction): RecurringBillTransaction
    {
        $orgExchangeRate = GetCurrencyExchange::run($recurringBillTransaction->organisation->currency, $recurringBillTransaction->fulfilment->shop->currency);
        $grpExchangeRate = GetCurrencyExchange::run($recurringBillTransaction->group->currency, $recurringBillTransaction->fulfilment->shop->currency);


        $recurringBillTransaction->update([
            'org_net_amount' => $recurringBillTransaction->net_amount * $orgExchangeRate,
            'grp_net_amount' => $recurringBillTransaction->net_amount * $grpExchangeRate,
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
