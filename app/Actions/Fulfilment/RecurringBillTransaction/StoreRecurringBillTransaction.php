<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 09:00:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\Fulfilment\RecurringBill\Hydrators\RecurringBillHydrateTransactions;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
use App\Models\Fulfilment\StoredItem;

class StoreRecurringBillTransaction extends OrgAction
{
    public function handle(RecurringBill $recurringBill, Pallet|StoredItem $item, array $modelData): RecurringBillTransaction
    {
        data_set($modelData, 'organisation_id', $recurringBill->organisation_id);
        data_set($modelData, 'group_id', $recurringBill->group_id);
        data_set($modelData, 'fulfilment_id', $recurringBill->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $recurringBill->fulfilment_customer_id);

        data_set($modelData, 'item_id', $item->id);
        data_set($modelData, 'item_type', class_basename($item));

        /** @var RecurringBillTransaction $recurringBillTransaction */
        $recurringBillTransaction = $recurringBill->transactions()->create($modelData);

        $item->update(['current_recurring_bill_id' => $recurringBill->id]);

        RecurringBillHydrateTransactions::dispatch($recurringBill)->delay(now()->addSeconds(2));


        return $recurringBillTransaction;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
        ];
    }

    public function action(RecurringBill $recurringBill, Pallet|StoredItem $item, array $modelData): RecurringBillTransaction
    {
        $this->asAction = true;

        $this->initialisationFromShop($recurringBill->fulfilment->shop, $modelData);
        return $this->handle($recurringBill, $item, $this->validatedData);
    }


}
