<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 12:00:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Inventory\OrgStock\UI\IndexOrgStocksInLocation;
use App\Models\Inventory\Location;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

class GetGoodsOutPackingProgress
{
    use AsObject;

    /**
     * @return array<int, array{id: int, code: string, slug: string, packed: int, total: int, is_current: bool}>
     */
    public function handle(Location $location): array
    {
        return DB::table('locations')
            ->leftJoin('location_org_stocks', function ($join) {
                $join->on('location_org_stocks.location_id', 'locations.id')
                    ->where('location_org_stocks.quantity', '>', 0);
            })
            ->where('locations.warehouse_id', $location->warehouse_id)
            ->where('locations.is_goods_out', true)
            ->whereNull('locations.deleted_at')
            ->groupBy('locations.id', 'locations.code', 'locations.slug')
            ->orderBy('locations.code')
            ->selectRaw('locations.id, locations.code, locations.slug, count(location_org_stocks.id) as total, count(location_org_stocks.id) FILTER (WHERE '.IndexOrgStocksInLocation::IS_PACKED_SQL.') as packed')
            ->get()
            ->map(fn ($row) => [
                'id'         => $row->id,
                'code'       => $row->code,
                'slug'       => $row->slug,
                'packed'     => (int) $row->packed,
                'total'      => (int) $row->total,
                'is_current' => $row->id == $location->id,
            ])
            ->all();
    }
}
