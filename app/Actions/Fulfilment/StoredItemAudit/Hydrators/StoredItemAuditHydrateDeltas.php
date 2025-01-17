<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 16-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\StoredItemAudit\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\StoredItemAudit;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class StoredItemAuditHydrateDeltas
{
    use AsAction;
    use WithEnumStats;

    private StoredItemAudit $StoredItemAudit;
    public function __construct(StoredItemAudit $StoredItemAudit)
    {
        $this->StoredItemAudit = $StoredItemAudit;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->StoredItemAudit->id))->dontRelease()];
    }

    public function handle(StoredItemAudit $storedItemAudit): void
    {

        $deltas = $storedItemAudit->deltas;

        $inProcessDeltas = $deltas->where('state', StoredItemAuditDeltaStateEnum::IN_PROCESS);
        $additionDeltas = $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::ADDITION);
        $subtractionDeltas = $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::SUBTRACTION);
        $checkDeltas = $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::CHECK);

        $stats = [
            'number_audited_pallets' => $deltas->pluck('pallet_id')->unique()->count(),
            'number_audited_stored_items' => $deltas->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_additions' => $additionDeltas->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_with_subtractions' => $subtractionDeltas->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_with_stock_checked' => $checkDeltas->pluck('stored_item_id')->unique()->count(),
            'number_associated_stored_items' => $inProcessDeltas->pluck('stored_items')->unique()->count(),
            'number_created_stored_items' => $inProcessDeltas->where('is_new_stored_item', true)->where('is_stored_item_new_in_pallet', true)->pluck('stored_items')->unique()->count(),
        ];

        $storedItemAudit->update($stats);
    }


    public string $commandSignature = 'hydrate:stored_item_audit_deltas';

    public function asCommand($command)
    {
        $storedItemAudits = StoredItemAudit::all();

        foreach ($storedItemAudits as $storedItemAudit) {
            $this->handle($storedItemAudit);
        }
    }
}
