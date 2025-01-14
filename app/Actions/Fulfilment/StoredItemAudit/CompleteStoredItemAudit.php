<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit;

use App\Actions\Fulfilment\StoredItem\AttachStoredItemToPallet;
use App\Actions\Fulfilment\StoredItem\DetachStoredItemToPallet;
use App\Actions\Fulfilment\StoredItem\UpdateStoredItemToPallet;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemAuditsResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use Lorisleiva\Actions\ActionRequest;

class CompleteStoredItemAudit extends OrgAction
{
    use WithActionUpdate;

    private FulfilmentCustomer $fulfilmentCustomer;
    private StoredItemAudit $storedItemAudit;

    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAudit
    {
        foreach ($storedItemAudit->deltas as $storedItemAuditDelta) {
            $pallet = $storedItemAuditDelta->pallet;

            $storedItemAuditDelta->update(
                [
                    'state' => StoredItemAuditDeltaStateEnum::COMPLETED
                ]
            );

            if ($storedItemAuditDelta->audit_type === StoredItemAuditDeltaTypeEnum::SET_UP) {
                AttachStoredItemToPallet::run($pallet, $storedItemAuditDelta->storedItem, $storedItemAuditDelta->quantity);
            } elseif ($pallet->storedItems()->where('stored_item_id', $storedItemAuditDelta->stored_item_id)->first()->quantity) {
                DetachStoredItemToPallet::run($pallet, $storedItemAuditDelta->storedItem);
            } else {
                UpdateStoredItemToPallet::run($pallet, $storedItemAuditDelta->storedItem, $storedItemAuditDelta->quantity);
            }
        }

        $modelData['state'] = StoredItemAuditStateEnum::COMPLETED;

        return $this->update($storedItemAudit, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAudit
    {
        $this->fulfilmentCustomer = $storedItemAudit->fulfilmentCustomer;
        $this->storedItemAudit    = $storedItemAudit;
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $request);

        return $this->handle($storedItemAudit, $this->validatedData);
    }

    public function jsonResponse(StoredItemAudit $storedItem): StoredItemAuditsResource
    {
        return new StoredItemAuditsResource($storedItem);
    }
}
