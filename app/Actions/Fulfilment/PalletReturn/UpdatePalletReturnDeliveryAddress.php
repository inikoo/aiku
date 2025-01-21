<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletReturnDeliveryAddress extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentcustomer, PalletReturn $palletReturn, array $modelData): void
    {
        if (Arr::get($modelData, 'is_collection', true)) {
            $addressData = Arr::get($modelData, 'address');
            $countryCode = Country::find($addressData['country_id'])->code;
            data_set($addressData, 'country_code', $countryCode);
            $label = isset($addressData['label']) ? $addressData['label'] : null;
            unset($addressData['label']);
            unset($addressData['can_edit']);
            unset($addressData['can_delete']);
            $updatedAddress     = UpdateAddress::run(Address::find(Arr::get($addressData, 'id')), $addressData);
            $pivotData['label'] = $label;
            $palletReturn->addresses()->updateExistingPivot(
                $updatedAddress->id,
                $pivotData
            );
        } else {
            DeletePalletReturnAddress::run($palletReturn, Address::find($fulfilmentcustomer->palletReturn->delivery_address_id));
        }
    }

    public function rules(): array
    {
        return [
            'address'             => ['sometimes'],
            'is_collection'       => ['sometimes', 'boolean'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($this->scope instanceof FulfilmentCustomer) {
            return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
        } else {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }
        return false;
    }

    public function asController(FulfilmentCustomer $fulfilmentcustomer, PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->scope = $fulfilmentcustomer;
        $this->initialisationFromShop($fulfilmentcustomer->shop, $request);

        $this->handle($fulfilmentcustomer, $palletReturn, $this->validatedData);
    }

    // public function fromFulfilmentFulfilmentCustomer(FulfilmentFulfilmentCustomer $fulfilmentFulfilmentCustomer, ActionRequest $request): void
    // {
    //     $this->scope = $fulfilmentFulfilmentCustomer;
    //     $this->initialisationFromFulfilment($fulfilmentFulfilmentCustomer->fulfilment, $request);

    //     $this->handle($fulfilmentFulfilmentCustomer->fulfilmentcustomer, $this->validatedData);
    // }

    public function action(FulfilmentCustomer $fulfilmentcustomer, PalletReturn $palletReturn, $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($fulfilmentcustomer->shop, $modelData);

        $this->handle($fulfilmentcustomer, $palletReturn, $this->validatedData);
    }
}
