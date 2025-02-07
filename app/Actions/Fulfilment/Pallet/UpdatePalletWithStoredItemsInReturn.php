<?php
/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-10h-44m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletReturn\AutoAssignServicesToPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletReturnItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsCommand;

class UpdatePalletWithStoredItemsInReturn extends OrgAction
{
    use AsCommand;
    use WithActionUpdate;

    private PalletReturnItem $parent;

    public function handle(PalletReturnItem $palletReturnItem, array $modelData)
    {
        $this->update($palletReturnItem, $modelData);
    }

    public function rules(): array
    {
        return [
            'quantity_ordered' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
        ];
    }


    public function action(PalletReturnItem $palletReturnItem, array $modelData, int $hydratorsDelay = 0)
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturnItem;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function asController(PalletReturnItem $palletReturnItem, array $modelData)
    {
        $this->parent         = $palletReturnItem;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturnItem, $this->validatedData);
    }

    public function prepareForValidation()
    {
        $quantity_ordered = $this->get('quantity_ordered');


        $palletReturnItem = PalletReturnItem::find($this->parent->id);

        if ($quantity_ordered > $palletReturnItem->palletStoredItem->quantity) {
            throw ValidationException::withMessages([
                'reference' => ['Quantity ordered cannot be greater than the quantity stored.'],
            ]);
        }
    }
}
