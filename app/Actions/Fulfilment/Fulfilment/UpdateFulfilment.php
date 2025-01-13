<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithGetRecurringBillEndDate;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Validation\Rules\File;

class UpdateFulfilment extends OrgAction
{
    use WithActionUpdate;
    use WithGetRecurringBillEndDate;

    protected Fulfilment $fulfilment;

    public function handle(Fulfilment $fulfilment, array $modelData): Fulfilment
    {
        $settings       = $fulfilment->settings;
        $updateSettings = false;

        $endDate = null;

        if (Arr::exists($modelData, 'weekly_cut_off_day')) {
            $settings['rental_agreement_cut_off']['weekly']['day'] = $modelData['weekly_cut_off_day'];
            $updateSettings                                        = true;
            data_forget($modelData, 'weekly_cut_off_day');
            $this->getEndDateWeekly(now(), $settings['rental_agreement_cut_off']['weekly']);
        }

        if (Arr::exists($modelData, 'monthly_cut_off')) {
            $settings['rental_agreement_cut_off']['monthly']['day']      = $modelData['monthly_cut_off']['date'];
            $settings['rental_agreement_cut_off']['monthly']['workdays'] = $modelData['monthly_cut_off']['isWeekdays'];
            $updateSettings                                              = true;
            data_forget($modelData, 'monthly_cut_off');
            // data_forget($modelData, 'monthly_only_weekdays');
        }

        if ($updateSettings) {
            $modelData['settings'] = $settings;
        }

        $shopData = Arr::only($modelData, [
            'name',
            'code',
            'currency_id',
            'country_id',
            'language_id',
            'contact_name',
            'company_name',
            'email',
            'phone',
            'address',
            'registration_number',
            'vat_number',
            'image',
        ]);

        $modelData = Arr::except($modelData, array_keys($shopData));

        $fulfilment = $this->update($fulfilment, $modelData, ['settings']);

        if (Arr::get($modelData, 'update_all', true)) {

            $recurringBills = $fulfilment->recurringBills->where('status', RecurringBillStatusEnum::CURRENT);

            foreach ($recurringBills as $recurringBill) {
                $startDate = Carbon::parse($recurringBill->start_date);
                $rentalAgreement = $recurringBill->rentalAgreement;
                $endDate = $this->getEndDate(
                    $startDate->copy(),
                    Arr::get(
                        $settings,
                        'rental_agreement_cut_off.'.$rentalAgreement->billing_cycle->value
                    )
                );
                $recurringBill->end_date = $endDate;
                $recurringBill->save();
            }
        }

        $shop = UpdateShop::make()->action($fulfilment->shop, $shopData);

        return $fulfilment;


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
            'weekly_cut_off_day'     => ['sometimes','string', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']) ],
            'monthly_cut_off'        => [
                'sometimes',
                'array'
            ],
            'monthly_cut_off.date' => [
                'sometimes',
                'integer',
                'min:1',
                'max:31'
            ],
            'monthly_cut_off.isWeekdays' => [
                'sometimes',
                'boolean',
            ],
            'update_all' => [
                'sometimes',
                'boolean',
            ],
            'name'                     => ['sometimes', 'required', 'string', 'max:255'],
            'code'                     => [
                'sometimes',
                'required',
                'max:8',
                'alpha_dash',
                new IUnique(
                    table: 'shops',
                    extraConditions: [

                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->shop->id
                        ],
                    ]
                ),

            ],
            'currency_id'              => ['sometimes', 'required', 'exists:currencies,id'],
            'country_id'               => ['sometimes', 'required', 'exists:countries,id'],
            'language_id'              => ['sometimes', 'required', 'exists:languages,id'],
            'contact_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email'],
            'phone'                    => ['sometimes', 'nullable'],
            'address'                  => ['sometimes', 'required', new ValidAddress()],
            'registration_number'      => ['sometimes', 'string'],
            'vat_number'               => ['sometimes', 'string'],
            'image'       => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ]
        ];
    }

    public function asController(Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {

        $this->fulfilment = $fulfilment;

        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilment, $this->validatedData);
    }

    public function action(Fulfilment $fulfilment, array $modelData, int $hydratorsDelay = 0): Fulfilment
    {
        $this->fulfilment     = $fulfilment;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($fulfilment, $modelData);

        return $this->handle($fulfilment, $this->validatedData);
    }
}
