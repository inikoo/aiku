<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 12:24:17 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\RecurringBillTransaction\StoreRecurringBillTransaction;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\RecurringBill;

class FindStoredPalletsAndAttachThemToNewRecurringBill extends OrgAction
{
    public function handle(RecurringBill $recurringBill): RecurringBill
    {
        foreach ($recurringBill->fulfilmentCustomer->pallets as $pallet) {
            if ($pallet->state == PalletStateEnum::STORING and $pallet->storing_at  and !$pallet->current_recurring_bill_id
            ) {
                StoreRecurringBillTransaction::make()->action(
                    $recurringBill,
                    $pallet,
                    [
                        'start_date' => $pallet->storing_at
                    ]
                );
            }
        }

        return $recurringBill;
    }


    public function action(RecurringBill $recurringBill): RecurringBill
    {
        $this->asAction = true;

        $this->initialisationFromFulfilment($recurringBill->fulfilment, []);

        return $this->handle($recurringBill);
    }


}
