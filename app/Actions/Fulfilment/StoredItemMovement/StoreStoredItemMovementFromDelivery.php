<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-10h-31m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemMovement;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Models\Fulfilment\StoredItemAuditDelta;
use App\Models\Fulfilment\StoredItemMovement;
use Illuminate\Support\Arr;

class StoreStoredItemMovementFromDelivery extends OrgAction
{
    public function handle(StoredItemAuditDelta $storedItemAuditDelta, array $modelData): ?StoredItemMovement
    {

        $quantity = Arr::pull($modelData, 'quantity', 0);

        data_set($modelData, 'stored_item_id', $storedItemAuditDelta->stored_item_id);
        data_set($modelData, 'pallet_id', $storedItemAuditDelta->pallet_id);
        data_set($modelData, 'stored_item_audit_id', $storedItemAuditDelta->stored_item_audit_id);
        data_set($modelData, 'stored_item_audit_delta_id', $storedItemAuditDelta->id);

        data_set($modelData, 'type', StoredItemMovementTypeEnum::RECEIVED);
        data_set($modelData, 'quantity', $quantity);
        data_set($modelData, 'moved_at', now());

        return StoredItemMovement::create($modelData);
    }
}
