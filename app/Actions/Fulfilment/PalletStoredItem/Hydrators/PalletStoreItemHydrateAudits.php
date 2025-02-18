<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Feb 2025 23:29:35 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletStoredItem\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Models\Fulfilment\PalletStoredItem;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class PalletStoreItemHydrateAudits extends HydrateModel
{
    use WithActionUpdate;
    use WithEnumStats;

    private PalletStoredItem $palletStoredItem;

    public function __construct(PalletStoredItem $palletStoredItem)
    {
        $this->palletStoredItem = $palletStoredItem;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->palletStoredItem->id))->dontRelease()];
    }

    public function handle(PalletStoredItem $palletStoredItem): void
    {
        $lastAuditAt      = null;
        $lastAuditId      = null;
        $lastAuditDeltaId = null;
        if ($latestAuditDelta = DB::table('stored_item_audit_deltas')
            ->where('stored_item_id', $palletStoredItem->stored_item_id)
            ->where('pallet_id', $palletStoredItem->pallet_id)
            ->where('state', StoredItemAuditDeltaStateEnum::COMPLETED->value)->latest()->first()) {
            $lastAuditDeltaId = $latestAuditDelta->id;
            $lastAuditAt      = $latestAuditDelta->audited_at;

            if ($latestAuditDelta->stored_item_audit_id) {
                $lastAuditId = $latestAuditDelta->stored_item_audit_id;
            } elseif ($latestAuditDeltaWithAudit = DB::table('stored_item_audit_deltas')
                ->where('stored_item_id', $palletStoredItem->stored_item_id)
                ->where('pallet_id', $palletStoredItem->pallet_id)
                ->whereNotNull('stored_item_audit_id')
                ->where('state', StoredItemAuditDeltaStateEnum::COMPLETED->value)
                ->latest()->first()) {
                $lastAuditId = $latestAuditDeltaWithAudit->stored_item_audit_id;
            }
        }


        $stats = [
            'number_audits'                   => DB::table('stored_item_audit_deltas')
                ->where('stored_item_id', $palletStoredItem->stored_item_id)
                ->where('pallet_id', $palletStoredItem->pallet_id)
                ->where('state', StoredItemAuditDeltaStateEnum::COMPLETED->value)->count(),
            'last_audit_at'                   => $lastAuditAt,
            'last_stored_item_audit_delta_id' => $lastAuditDeltaId,
            'last_stored_item_audit_id'       => $lastAuditId,

        ];



        $palletStoredItem->update($stats);
    }
}
