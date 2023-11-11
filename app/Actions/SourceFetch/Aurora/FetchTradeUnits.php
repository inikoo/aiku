<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:28:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Models\Goods\TradeUnit;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchTradeUnits extends FetchAction
{
    public string $commandSignature = 'fetch:trade-units {tenants?*} {--s|source_id=} {--d|db_suffix=}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?TradeUnit
    {
        if ($tradeUnitData = $organisationSource->fetchTradeUnit($organisationSourceId)) {
            if ($tradeUnit = TradeUnit::withTrashed()->where('source_id', $tradeUnitData['trade_unit']['source_id'])
                ->first()) {
                $tradeUnit = UpdateTradeUnit::run(
                    tradeUnit: $tradeUnit,
                    modelData: $tradeUnitData['trade_unit'],
                );
            } else {
                $tradeUnit = StoreTradeUnit::run(
                    modelData: $tradeUnitData['trade_unit']
                );
            }

            return $tradeUnit;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU as source_id');

        $query->orderBy('source_id');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Part Dimension');

        return $query->count();
    }
}
