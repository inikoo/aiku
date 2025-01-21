<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Search\PalletReturnRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Rules\ValidAddress;
use Lorisleiva\Actions\ActionRequest;

class AddAddressToPalletReturn extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        if ($modelData == []) {
            $address = $fulfilmentCustomer->customer->address->toArray();
            $palletReturn = $this->addAddressToModelFromArray(
                model: $palletReturn,
                addressData: $address,
                scope: 'delivery',
                updateLocation: false,
                updateAddressField:false
            );
        } else {
            $palletReturn = $this->addAddressToModelFromArray(
                model: $palletReturn,
                addressData: $modelData['delivery_address'],
                scope: 'delivery',
                updateLocation: false,
                updateAddressField:false
            );
        }

        $palletReturn->refresh();
        $palletReturn->update(['delivery_address_id' =>  null]);
        PalletReturnRecordSearch::dispatch($palletReturn);

        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'delivery_address'         => ['sometimes', new ValidAddress()],
        ];
    }


    public function asController(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $palletReturn, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0, bool $strict = true): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);


        return $this->handle($fulfilmentCustomer, $palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $customer = $request->user()->customer;
        $palletReturn = $request->user()->customer->palletReturn;

        $this->initialisation($request->get('website')->organisation, $request);

        return $this->handle($customer, $palletReturn, $this->validatedData);
    }


    public function jsonResponse(PalletReturn $palletReturn): PalletReturnResource
    {
        return new PalletReturnResource($palletReturn);
    }
}
