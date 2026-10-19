<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Actions\Inventory\OrgStock\NotifyOrgStocksBelowMinimumStock;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\Warehouse;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydrateOrgStocksBelowMinimumStock implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'hydrators-slave';

    public function getJobUniqueId(Warehouse $warehouse): string
    {
        return $warehouse->id;
    }

    public static function belowMinimumStock(Builder $query, int $organisationId): Builder
    {
        return $query->where('org_stocks.organisation_id', $organisationId)
            ->where('org_stocks.state', OrgStockStateEnum::ACTIVE)
            ->whereNotNull('org_stocks.minimum_stock')
            ->whereColumn('org_stocks.quantity_in_locations', '<', 'org_stocks.minimum_stock');
    }

    public function handle(Warehouse $warehouse): void
    {
        $warehouse->stats()->update([
            'number_org_stocks_below_minimum_stock' => self::belowMinimumStock(OrgStock::query(), $warehouse->organisation_id)->count()
        ]);

        NotifyOrgStocksBelowMinimumStock::run($warehouse);
    }
}
