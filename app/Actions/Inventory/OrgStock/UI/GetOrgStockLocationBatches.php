<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 19:10:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Models\Inventory\OrgStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgStockLocationBatches
{
    use AsObject;

    /**
     * Batches on the shelves of each location, keyed by location id, earliest best-before first.
     *
     * @return Collection<int, array<int, array{batch_code_id: int, code: string, expiry_date: ?string, quantity: float}>>
     */
    public function handle(OrgStock $orgStock): Collection
    {
        return DB::table('org_stock_movement_batches')
            ->join('batch_codes', 'batch_codes.id', '=', 'org_stock_movement_batches.batch_code_id')
            ->where('org_stock_movement_batches.org_stock_id', $orgStock->id)
            ->groupBy('org_stock_movement_batches.location_id', 'batch_codes.id', 'batch_codes.code', 'batch_codes.expiry_date')
            ->havingRaw('sum(org_stock_movement_batches.quantity) > 0.000001')
            ->orderByRaw('batch_codes.expiry_date asc nulls last, batch_codes.id')
            ->selectRaw('org_stock_movement_batches.location_id, batch_codes.id as batch_code_id, batch_codes.code, batch_codes.expiry_date, sum(org_stock_movement_batches.quantity) as quantity')
            ->get()
            ->groupBy('location_id')
            ->map(fn (Collection $batches) => $batches->map(fn ($batch) => [
                'batch_code_id' => $batch->batch_code_id,
                'code'          => $batch->code,
                'expiry_date'   => $batch->expiry_date,
                'quantity'      => (float) $batch->quantity,
            ])->values()->all());
    }
}
