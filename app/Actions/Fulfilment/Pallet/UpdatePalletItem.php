<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Http\Resources\Fulfilment\PalletReturnItemsResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletItem extends OrgAction
{
    use WithActionUpdate;


    private PalletReturnItem $pallet;

    public function handle(PalletReturnItem $palletReturnItem, array $modelData): PalletReturnItem
    {
        if($modelData['state'] == PalletReturnItemStateEnum::PICKED->value) {
            data_set($modelData, 'picked_from_location_id', $palletReturnItem->pallet->location_id);
        }

        $this->update($palletReturnItem, $modelData);

        data_forget($modelData, 'picked_from_location_id');
        data_forget($modelData, 'state');

        UpdatePallet::run($palletReturnItem->pallet, $modelData);

        return $palletReturnItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'state'              => [
                'sometimes',
                Rule::enum(PalletReturnItemStateEnum::class)
            ]
        ];
    }

    public function fromRetina(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;
        $this->pallet       = $palletReturnItem;

        $this->initialisation($request->get('website')->organisation, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->pallet = $palletReturnItem;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function fromApi(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->pallet = $palletReturnItem;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function action(PalletReturnItem $palletReturnItem, array $modelData, int $hydratorsDelay = 0): PalletReturnItem
    {
        $this->pallet         = $palletReturnItem;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function jsonResponse(PalletReturnItem $palletReturnItem): PalletReturnItemsResource
    {
        return new PalletReturnItemsResource($palletReturnItem);
    }
}
