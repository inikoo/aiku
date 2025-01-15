<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\StoredItemAuditDelta\StoreStoredItemAuditDelta;
use App\Actions\Fulfilment\StoredItemAuditDelta\UpdateStoredItemAuditDelta;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItemAudit;
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

    public function handle(Pallet $pallet, StoredItemAudit $storedItemAudit, array $modelData): void
    {
        foreach (Arr::get($modelData, 'stored_item_ids', []) as $storedItemId => $auditData) {
            $storedItemExist = $pallet->storedItems()->where(
                'stored_item_id',
                $storedItemId
            )->exists();
            $originalQty     = 0;
            if ($storedItemExist) {
                $originalQty = $pallet->storedItems()->where(
                    'stored_item_id',
                    $storedItemId
                )->count();
            }

            if ($storedItemExist) {
                if ($originalQty > $auditData['quantity']) {
                    $type = StoredItemAuditDeltaTypeEnum::SUBTRACTION;
                } elseif ($originalQty < $auditData['quantity']) {
                    $type = StoredItemAuditDeltaTypeEnum::ADDITION;
                } else {
                    $type = StoredItemAuditDeltaTypeEnum::NO_CHANGE;
                }
            } else {
                $type = StoredItemAuditDeltaTypeEnum::SET_UP;
            }

            $storedItemAuditDelta = $storedItemAudit->deltas()->where('pallet_id', $pallet->id)->where('stored_item_id', $storedItemId)->first();
            if ($storedItemAuditDelta) {
                UpdateStoredItemAuditDelta::run($storedItemAuditDelta, [
                    'audited_quantity' => $auditData['quantity'],
                    'audited_at'       => now(),
                    'type'             => $type,
                    'state'            => StoredItemAuditDeltaStateEnum::IN_PROCESS
                ]);
            } else {
                StoreStoredItemAuditDelta::run($storedItemAudit, [
                    'group_id'          => $pallet->group_id,
                    'organisation_id'   => $pallet->organisation_id,
                    'pallet_id'         => $pallet->id,
                    'stored_item_id'    => $storedItemId,
                    'original_quantity' => $originalQty,
                    'audited_quantity'  => $auditData['quantity'],
                    'audited_at'        => now(),
                    'audit_type'              => $type,
                    'state'             => StoredItemAuditDeltaStateEnum::IN_PROCESS
                ]);
            }
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

    public function asController(Pallet $pallet, StoredItemAudit $storedItemAudit, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->initialisation($pallet->organisation, $request);

        $this->handle($pallet, $storedItemAudit, $this->validatedData);
    }

    public function action(Pallet $pallet, StoredItemAudit $storedItemAudit, $modelData): void
    {
        $this->asAction           = true;
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->initialisation($pallet->organisation, $modelData);

        $this->handle($pallet, $storedItemAudit, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
