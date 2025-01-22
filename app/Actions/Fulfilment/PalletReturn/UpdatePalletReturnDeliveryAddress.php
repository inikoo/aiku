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
    public function handle(PalletReturn $palletReturn, array $modelData): void
    {
        $isCollection = Arr::get($modelData, 'is_collection', true);
        $palletReturn->update(['is_collection' => $isCollection]);

        if (!$isCollection) {
            $addressData = Arr::get($modelData, 'address');
            $countryCode = Country::find($addressData['country_id'])->code;
            data_set($addressData, 'country_code', $countryCode);
            unset($addressData['label']);
            unset($addressData['can_edit']);
            unset($addressData['can_delete']);
            UpdateAddress::run(Address::find(Arr::get($addressData, 'id')), $addressData);
        } elseif ($palletReturn->delivery_address_id) {
            DeletePalletReturnAddress::run($palletReturn, Address::find($palletReturn->delivery_address_id));
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

    public function asController(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->scope = $palletReturn->customer;
        $this->initialisationFromShop($palletReturn->shop, $request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($palletReturn->shop, $modelData);

        $this->handle($palletReturn, $this->validatedData);
    }
}
