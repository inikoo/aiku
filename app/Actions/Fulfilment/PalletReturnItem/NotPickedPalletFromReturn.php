<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jun 2024 10:36:39 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturnItem;

use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletReturn\UpdatePalletReturnStateFromItems;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Http\Resources\Fulfilment\PalletReturnItemsResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturnItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class NotPickedPalletFromReturn extends OrgAction
{
    use WithActionUpdate;


    private PalletReturnItem $palletReturnItem;

    public function handle(PalletReturnItem $palletReturnItem, $modelData): PalletReturnItem
    {
        $palletReturnItem = $this->update($palletReturnItem, [
            'state' => PalletReturnItemStateEnum::NOT_PICKED
        ], ['data']);

        UpdatePallet::run($palletReturnItem->pallet, [
            'state'              => Arr::get($modelData, 'state'),
            'status'             => PalletStatusEnum::INCIDENT,
            'set_as_incident_at' => now(),
            'incident_report'    => [
                'notes' => Arr::get($modelData, 'notes')
            ]
        ]);

        UpdatePalletReturnStateFromItems::run($palletReturnItem->palletReturn);

        return $palletReturnItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");
    }

    public function rules(): array
    {
        return [
            'state'   => ['required', Rule::enum(PalletStateEnum::class)],
            'notes'   => ['required', 'string']
        ];
    }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->initialisationFromWarehouse($palletReturnItem->palletReturn->warehouse, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function action(PalletReturnItem $palletReturnItem, $state, int $hydratorsDelay = 0): PalletReturnItem
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromWarehouse($palletReturnItem->palletReturn->warehouse, []);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function jsonResponse(Pallet $palletReturnItem): PalletReturnItemsResource
    {
        return new PalletReturnItemsResource($palletReturnItem);
    }
}