<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Apr 2024 17:12:30 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\RecurringBill;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Market\RecurringBill;
use App\Models\Market\RentalAgreement;

class StoreRecurringBill extends OrgAction
{
    public function handle(RentalAgreement $rentalAgreement, array $modelData): RecurringBill
    {

        data_set($modelData, 'organisation_id', $rentalAgreement->organisation_id);
        data_set($modelData, 'group_id', $rentalAgreement->group_id);
        data_set($modelData, 'fulfilment_id', $rentalAgreement->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $rentalAgreement->fulfilment_customer_id);

        data_set(
            $modelData,
            'reference',
            GetSerialReference::run(
                container: $rentalAgreement->fulfilmentCustomer,
                modelType: SerialReferenceModelEnum::RECURRING_BILL
            )
        );

        /** @var RecurringBill $recurringBill */
        $recurringBill=$rentalAgreement->recurringBills()->create($modelData);
        $rentalAgreement->fulfilmentCustomer->update(['current_recurring_bill_id'=>$recurringBill->id]);

        return $recurringBill;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date'],
        ];

    }

    public function action(RentalAgreement $rentalAgreement, array $modelData): RecurringBill
    {
        $this->asAction       = true;

        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $modelData);
        return $this->handle($rentalAgreement, $this->validatedData);
    }




}
