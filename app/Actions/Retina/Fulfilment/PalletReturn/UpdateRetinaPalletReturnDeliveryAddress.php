<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\RetinaAction;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaPalletReturnDeliveryAddress extends RetinaAction
{
    public function handle(PalletReturn $palletReturn, array $modelData): void
    {
        $addressData = Arr::get($modelData, 'address');
        if ($addressData) {
            $countryCode = Country::find($addressData['country_id'])->code;
            data_set($addressData, 'country_code', $countryCode);
            unset($addressData['id']);
            unset($addressData['label']);
            unset($addressData['can_edit']);
            unset($addressData['can_delete']);
            UpdateAddress::run($palletReturn->deliveryAddress, $addressData);
        }
    }

    public function rules(): array
    {
        return [
            'address'             => ['sometimes'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return false;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->parent = $palletReturn;
        $this->initialisation($request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData): void
    {
        $this->action = true;
        $this->initialisationFulfilmentActions($palletReturn->fulfilmentCustomer, $modelData);

        $this->handle($palletReturn, $this->validatedData);
    }
}
