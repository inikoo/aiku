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
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItemAudit;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class StoredItemAuditHydrateDeltas
{
    use AsAction;
    use WithEnumStats;

    private PalletReturn $palletReturn;
    public function __construct(PalletReturn $palletReturn)
    {
        $this->palletReturn = $palletReturn;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->palletReturn->id))->dontRelease()];
    }

    public function handle(StoredItemAudit $storedItemAudit): void
    {

        $deltas = $storedItemAudit->deltas;

        $stats = [
            'number_audited_pallets' => $deltas->pluck('pallet_id')->unique()->count(),
            'number_audited_stored_items' => $deltas->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_additions' => $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::ADDITION)->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_with_subtractions' => $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::SUBTRACTION)->pluck('stored_item_id')->unique()->count(),
            'number_audited_stored_items_with_with_stock_checked' => $deltas->where('audit_type', StoredItemAuditDeltaTypeEnum::CHECK)->pluck('stored_item_id')->unique()->count(),
            'number_associated_stored_items' => $deltas->where('state', StoredItemAuditDeltaStateEnum::IN_PROCESS)->pluck('pallet_id')->unique()->count(),
            'number_created_stored_items' => $deltas->where('state', StoredItemAuditDeltaStateEnum::IN_PROCESS)->filter(function ($delta) {
                return $delta->storedItem->state === StoredItemStateEnum::IN_PROCESS;
            })->pluck('pallet_id')->unique()->count(),
        ];

        $storedItemAudit->update($stats);
    }


    public string $commandSignature = 'hydrate:stored_item_audit_deltas';

    public function asCommand($command)
    {
        $test = StoredItemAudit::find(4);

        $this->handle($test);
    }
}
