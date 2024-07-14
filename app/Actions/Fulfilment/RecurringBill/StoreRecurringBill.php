<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateRecurringBills;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateRecurringBills;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRecurringBills;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRecurringBills;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RentalAgreement;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StoreRecurringBill extends OrgAction
{
    public function handle(RentalAgreement $rentalAgreement, array $modelData): RecurringBill
    {

        //todo: get tax category from a real action #546
        data_set($modelData, 'tax_category_id', 1, overwrite: false);
        data_set($modelData, 'currency_id', $rentalAgreement->fulfilment->shop->currency_id, overwrite: false);

        data_set($modelData, 'organisation_id', $rentalAgreement->organisation_id);
        data_set($modelData, 'group_id', $rentalAgreement->group_id);
        data_set($modelData, 'fulfilment_id', $rentalAgreement->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $rentalAgreement->fulfilment_customer_id);


        if(!Arr::exists($modelData, 'start_end')) {

            $endDate= $this->getEndDate(
                $modelData['start_date']->copy(),
                Arr::get(
                    $rentalAgreement->fulfilment->settings,
                    'rental_agreement_cut_off.'.$rentalAgreement->billing_cycle->value
                )
            );

            data_set($modelData, 'end_date', $endDate);


        }

        data_set(
            $modelData,
            'reference',
            GetSerialReference::run(
                container: $rentalAgreement->fulfilmentCustomer,
                modelType: SerialReferenceModelEnum::RECURRING_BILL
            )
        );



        /** @var RecurringBill $recurringBill */
        $recurringBill = $rentalAgreement->recurringBills()->create($modelData);
        $recurringBill->stats()->create();
        StoreStrayRecurringBillTransactionables::run($recurringBill);


        GroupHydrateRecurringBills::dispatch($rentalAgreement->group);
        OrganisationHydrateRecurringBills::dispatch($rentalAgreement->organisation);
        FulfilmentHydrateRecurringBills::dispatch($rentalAgreement->fulfilment);
        FulfilmentCustomerHydrateRecurringBills::dispatch($rentalAgreement->fulfilmentCustomer);


        return $recurringBill;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date'   => ['sometimes', 'required', 'date', 'gte:start_date'],
        ];
    }

    public function action(RentalAgreement $rentalAgreement, array $modelData): RecurringBill
    {
        $this->asAction = true;

        $this->initialisationFromShop($rentalAgreement->fulfilment->shop, $modelData);

        return $this->handle($rentalAgreement, $this->validatedData);
    }

    public string $commandSignature = 'recurring-bill:create {rental-agreement}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $rentalAgreement = RentalAgreement::where('slug', $command->argument('rental-agreement'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        try {
            $this->initialisationFromFulfilment($rentalAgreement->fulfilment, []);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $proforma = $this->handle($rentalAgreement, modelData: $this->validatedData);

        $command->info("Recurring bill $proforma->slug created successfully 🎉");

        return 0;
    }

    public function getEndDate(Carbon $startDate, array $setting): Carbon
    {


        return match (Arr::get($setting, 'type')) {
            'weekly' => $this->getEndDateWeekly($startDate, $setting),
            default  => $this->getEndDateMonthly($startDate, $setting),
        };
    }

    public function getEndDateMonthly(Carbon $startDate, array $setting): Carbon
    {
        return  $startDate->addMonth();
    }

    public function getEndDateWeekly(Carbon $startDate, array $setting): Carbon
    {
        return  $startDate->addWeek();
    }

}
