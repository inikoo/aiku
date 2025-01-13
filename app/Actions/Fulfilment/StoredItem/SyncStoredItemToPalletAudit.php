<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncStoredItemToPalletAudit extends OrgAction
{
    use AsAction;
    use WithAttributes;

    protected FulfilmentCustomer $fulfilmentCustomer;
    protected Fulfilment $fulfilment;

    public function handle(Pallet $pallet, array $modelData): void
    {
        //SyncStoredItemToPallet::run($pallet, $modelData);
dd($modelData);

        foreach (Arr::get($modelData, 'stored_item_ids', []) as $storedItemId => $auditData) {

            $originalQty = $pallet->storedItems()->where(
                'stored_item_id',
                $storedItemId
            )->count();


            $pallet->storedItemAuditDeltas()->updateOrCreate([
                'stored_item_id'    => $storedItemId,
                'organisation_id'   => $pallet->organisation_id,
            ], [
                'group_id'          => $pallet->group_id,
                'organisation_id'   => $pallet->organisation_id,
                'stored_item_id'    => $storedItemId,
                'original_quantity' => $originalQty,
                'audited_quantity'  => $auditData['quantity'] + $originalQty,
                'audited_at'        => now()
            ]);
        }
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
            'stored_item_ids'            => ['sometimes', 'array'],
            'stored_item_ids.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'stored_item_ids.*.quantity.required' => __('The quantity is required'),
            'stored_item_ids.*.quantity.integer'  => __('The quantity must be an integer'),
            'stored_item_ids.*.quantity.min'      => __('The quantity must be at least 1'),
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->initialisation($pallet->organisation, $request);

        $this->handle($pallet, $this->validatedData);
    }

    public function action(Pallet $pallet, $modelData): void
    {
        $this->asAction           = true;
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->initialisation($pallet->organisation, $modelData);

        $this->handle($pallet, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
