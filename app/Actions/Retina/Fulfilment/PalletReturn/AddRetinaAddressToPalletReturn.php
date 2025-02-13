<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Search\PalletReturnRecordSearch;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use App\Rules\ValidAddress;
use Lorisleiva\Actions\ActionRequest;

class AddRetinaAddressToPalletReturn extends RetinaAction
{
    use WithActionUpdate;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $addressData = $modelData['delivery_address'];
        unset($addressData['can_edit']);
        unset($addressData['can_delete']);
        data_set($addressData, 'group_id', $palletReturn->group_id);
        $add = Address::create($addressData);
        $palletReturn->refresh();
        $palletReturn->update([
            'delivery_address_id' => $add->id,
            'is_collection' => false
        ]);
        PalletReturnRecordSearch::dispatch($palletReturn);

        return $palletReturn;
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

    public function rules(): array
    {
        return [
            'delivery_address'         => ['sometimes', new ValidAddress()],
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $palletReturn;
        $this->initialisation($request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $this->action = true;
        $this->initialisationFulfilmentActions($palletReturn->fulfilmentCustomer, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function jsonResponse(PalletReturn $palletReturn): PalletReturnResource
    {
        return new PalletReturnResource($palletReturn);
    }
}
