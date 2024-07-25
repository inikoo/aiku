<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateRecurringBills;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateRecurringBills;
use App\Actions\Fulfilment\RecurringBill\Search\RecurringBillRecordSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Helpers\TaxCategory\GetTaxCategory;
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

        if (!Arr::exists($modelData, 'tax_category_id')) {
            data_set(
                $modelData,
                'tax_category_id',
                GetTaxCategory::run(
                    country: $this->organisation->country,
                    taxNumber: $rentalAgreement->fulfilmentCustomer->customer->taxNumber,
                    billingAddress: $rentalAgreement->fulfilmentCustomer->customer->address,
                    deliveryAddress: $rentalAgreement->fulfilmentCustomer->customer->address,
                )->id
            );
        }


        data_set($modelData, 'currency_id', $rentalAgreement->fulfilment->shop->currency_id, overwrite: false);

        data_set($modelData, 'organisation_id', $rentalAgreement->organisation_id);
        data_set($modelData, 'group_id', $rentalAgreement->group_id);
        data_set($modelData, 'fulfilment_id', $rentalAgreement->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $rentalAgreement->fulfilment_customer_id);

        if(!Arr::exists($modelData, 'start_end')) {
            if (!$modelData['start_date'] instanceof Carbon) {
                $modelData['start_date'] = Carbon::parse($modelData['start_date']);
            }
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
        $recurringBill->refresh();

        StoreStrayRecurringBillTransactionables::run($recurringBill);


        GroupHydrateRecurringBills::dispatch($recurringBill->group);
        OrganisationHydrateRecurringBills::dispatch($recurringBill->organisation);
        FulfilmentHydrateRecurringBills::dispatch($recurringBill->fulfilment);
        FulfilmentCustomerHydrateRecurringBills::dispatch($recurringBill->fulfilmentCustomer);

        RecurringBillRecordSearch::dispatch($recurringBill);

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
            /** @var RentalAgreement $rentalAgreement */
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
        $endDayOfMonth = $setting['day'];

        $endDate = $startDate->copy()->day($endDayOfMonth);

        if ($endDate->lt($startDate)) {
            $endDate->addMonth();
        }

        if ($endDate->diffInDays($startDate) < 4) {
            $endDate->addMonth();
        }

        return $endDate;
    }

    public function getEndDateWeekly(Carbon $startDate, array $setting): Carbon
    {
        $daysOfWeek = [
            'Sunday'    => Carbon::SUNDAY,
            'Monday'    => Carbon::MONDAY,
            'Tuesday'   => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday'  => Carbon::THURSDAY,
            'Friday'    => Carbon::FRIDAY,
            'Saturday'  => Carbon::SATURDAY,
        ];

        $endDayOfWeek = $daysOfWeek[$setting['day']];

        $endDate = $startDate->copy()->next($endDayOfWeek);

        if ($endDate->diffInDays($startDate) < 4) {
            $endDate = $endDate->addWeek();
        }

        return $endDate;
    }

}
