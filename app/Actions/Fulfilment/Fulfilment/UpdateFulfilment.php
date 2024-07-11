<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryStateFromItems;
use App\Actions\Fulfilment\PalletReturn\UpdatePalletReturnStateFromItems;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePallets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePallets;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilment extends OrgAction
{
    use WithActionUpdate;


    protected Fulfilment $fulfilment;

    public function handle(Fulfilment $fulfilment, array $modelData): Fulfilment
    {
        $monthlyDay = Arr::get($modelData, 'monthly_cut_off_day');
        $weeklyDay = Arr::get($modelData, 'weekly_cut_off_day');
        data_set($modelData, 'settings', [
            'rental_agreement_weekly_cut_off'=>[
                RentalAgreementBillingCycleEnum::WEEKLY->value=>[
                    'type'=>RentalAgreementBillingCycleEnum::WEEKLY->value,
                    'day'=> $weeklyDay ?? $fulfilment->settings['rental_agreement_weekly_cut_off']['weekly']['day']
                ],
                RentalAgreementBillingCycleEnum::MONTHLY->value=>[
                    'type'=>RentalAgreementBillingCycleEnum::MONTHLY->value,
                    'day'=> $monthlyDay ?? $fulfilment->settings['rental_agreement_weekly_cut_off']['monthly']['day'],
                    'workdays'=>false
                ]
            ]

        ]);
        Arr::forget($modelData,['weekly_cut_off_day','monthly_cut_off_day']);
        $fulfilment = $this->update($fulfilment, $modelData);

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
            'monthly_cut_off_day' => ['sometimes'],
            'weekly_cut_off_day'  => ['sometimes']
        ];
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
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
