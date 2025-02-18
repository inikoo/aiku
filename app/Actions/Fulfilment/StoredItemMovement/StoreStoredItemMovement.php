<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-10h-31m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemMovement;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Models\Fulfilment\StoredItemAuditDelta;
use App\Models\Fulfilment\StoredItemMovement;

class StoreStoredItemMovement extends OrgAction
{
    public function handle(StoredItemAuditDelta $storedItemAuditDelta): ?StoredItemMovement
    {
        data_set($modelData, 'stored_item_id', $storedItemAuditDelta->stored_item_id);
        data_set($modelData, 'pallet_id', $storedItemAuditDelta->pallet_id);

        if ($storedItemAuditDelta->audit_type == StoredItemAuditDeltaTypeEnum::NO_CHANGE) {
            return null;
        }
        data_set($modelData, 'stored_item_audit_id', $storedItemAuditDelta->stored_item_audit_id);
        data_set($modelData, 'stored_item_audit_delta_id', $storedItemAuditDelta->id);
        $type = match($storedItemAuditDelta->audit_type) {
            StoredItemAuditDeltaTypeEnum::ADDITION,StoredItemAuditDeltaTypeEnum::SET_UP => StoredItemMovementTypeEnum::AUDIT_ADDITION,
            StoredItemAuditDeltaTypeEnum::SUBTRACTION => StoredItemMovementTypeEnum::AUDIT_SUBTRACTION,
            StoredItemAuditDeltaTypeEnum::DELIVERY => StoredItemMovementTypeEnum::RECEIVED,
        };
        data_set($modelData, 'type', $type);
        $quantity = $storedItemAuditDelta->audited_quantity - $storedItemAuditDelta->original_quantity;
        data_set($modelData, 'quantity', $quantity);
        data_set($modelData, 'moved_at', now());

        return StoredItemMovement::create($modelData);
    }
}
