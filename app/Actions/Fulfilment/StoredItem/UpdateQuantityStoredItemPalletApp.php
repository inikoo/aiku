<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateQuantityStoredItemPalletApp extends OrgAction
{
    use AsAction;
    use WithAttributes;

    protected FulfilmentCustomer $fulfilmentCustomer;
    protected Fulfilment $fulfilment;

    public function handle(Pallet $pallet, StoredItem $storedItem, array $modelData): Pallet
    {
        /** @var Pallet $targetPallet */
        $targetPallet = Pallet::find($modelData['pallet_id']);

        $palletSource = $pallet->storedItems()->sum('quantity');
        $palletTarget = $pallet->storedItems()->sum('quantity');

        $pallet->storedItems()->sync([$storedItem->id => [
            'quantity' => $palletSource - $modelData['quantity']
        ]]);

        $targetPallet->storedItems()->sync([$storedItem->id => [
            'quantity' => $palletTarget + $modelData['quantity']
        ]]);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'pallet_id' => ['required', 'exists:pallets,id'],
            'quantity'  => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(Pallet $pallet, StoredItem $storedItem, ActionRequest $request): Pallet
    {
        $this->fulfilmentCustomer = $storedItem->fulfilmentCustomer;
        $this->fulfilment         = $storedItem->fulfilment;

        $this->initialisation($storedItem->organisation, $request);

        return $this->handle($pallet, $storedItem, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return PalletResource::make($pallet);
    }
}
