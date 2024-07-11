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


        $settings = $fulfilment->settings;
        $updateSettings = false;


        if(Arr::exists($modelData, 'weekly_cut_off_day')) {
            $settings['rental_agreement_cut_off']['weekly']['day'] = $modelData['weekly_cut_off_day'];
            $updateSettings = true;
            data_forget($modelData, 'weekly_cut_off_day');
        }

        if(Arr::exists($modelData, 'monthly_cut_off_day')) {
            $settings['rental_agreement_cut_off']['monthly']['day'] = $modelData['monthly_cut_off_day'];
            if(Arr::exists($modelData, 'monthly_only_weekdays')) {
                $settings['rental_agreement_cut_off']['monthly']['workdays'] = $modelData['monthly_only_weekdays'];
            }
            $updateSettings = true;
            data_forget($modelData, 'monthly_cut_off_day');
            data_forget($modelData, 'monthly_only_weekdays');
        }

        if($updateSettings){
            $modelData['settings'] = $settings;
        }



        return $this->update($fulfilment, $modelData, ['settings']);


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
            'weekly_cut_off_day'  => ['sometimes','string', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']) ],
            'monthly_cut_off_day'  => ['sometimes','integer', 'min:1', 'max:31'],
            'monthly_only_weekdays'  => ['sometimes','boolean'],
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
