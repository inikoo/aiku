<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-11h-59m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\Fulfilment\PalletStoredItem\SetPalletStoredItemQuantity;
use App\Actions\Fulfilment\StoredItemMovement\StoreStoredItemMovement;
use App\Actions\Fulfilment\StoredItemMovement\StoreStoredItemMovementFromDelivery;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletStoredItem;
use App\Models\Fulfilment\StoredItemAuditDelta;

class StoreStoredItemAuditDeltaFromDelivery extends OrgAction
{
    use WithActionUpdate;

    public function handle(PalletDelivery $palletDelivery, PalletStoredItem $palletStoredItem, array $modelData): StoredItemAuditDelta
    {
        data_set($modelData, 'group_id', $palletDelivery->group_id);
        data_set($modelData, 'organisation_id', $palletDelivery->organisation_id);
        data_set($modelData, 'audited_at', now());

        data_set($modelData, 'audit_type', StoredItemAuditDeltaTypeEnum::DELIVERY);
        data_set($modelData, 'state', StoredItemAuditDeltaStateEnum::COMPLETED);
        data_set($modelData, 'original_quantity', 0);

        data_set($modelData, 'is_new_stored_item', false);
        data_set($modelData, 'is_stored_item_new_in_pallet', true);
        data_set($modelData, 'pallet_id', $palletStoredItem->pallet_id);
        data_set($modelData, 'stored_item_id', $palletStoredItem->stored_item_id);
        data_set($modelData, 'audited_quantity', 0);

        $storedItemAuditDelta = StoredItemAuditDelta::create($modelData);

        StoreStoredItemMovementFromDelivery::run($storedItemAuditDelta,
            [
                'quantity'=>$palletStoredItem->quantity

            ]);

        $palletStoredItem = $this->update($palletStoredItem, [
            'number_audits' => $palletStoredItem->number_audits + 1,
            'last_audit_at' => now(),
            'last_stored_item_audit_delta_id' => $storedItemAuditDelta->id,
            'in_process' => false
        ]);

        $palletStoredItem->refresh();
        SetPalletStoredItemQuantity::run($palletStoredItem);

        return $storedItemAuditDelta;
    }
}
