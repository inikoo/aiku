<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:53:00 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\RecurringBill\Search\RecurringBillRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\RecurringBill;
use App\Rules\EndDateValidation;
use Lorisleiva\Actions\ActionRequest;

class UpdateRecurringBilling extends OrgAction
{
    use WithActionUpdate;


    public function handle(RecurringBill $recurringBill, array $modelData): RecurringBill
    {
        $recurringBill = $this->update($recurringBill, $modelData, ['data']);
        RecurringBillRecordSearch::dispatch($recurringBill);
        return $recurringBill;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'end_date'       => ['sometimes', 'date', new EndDateValidation()],
            'total'          => ['sometimes'],
            'payment'        => ['sometimes'],
            'net'            => ['sometimes'],
            'grp_net_amount' => ['sometimes'],
            'org_net_amount' => ['sometimes']
        ];
    }


    public function asController(RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisationFromFulfilment($recurringBill->fulfilment, $request);

        return $this->handle($recurringBill, $this->validatedData);
    }

    public function action(RecurringBill $recurringBill, array $modelData): RecurringBill
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($recurringBill->fulfilment, $modelData);

        return $this->handle($recurringBill, $this->validatedData);
    }


}
