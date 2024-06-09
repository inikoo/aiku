<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:28:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Models\Goods\TradeUnit;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraTradeUnits extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:trade-units {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?TradeUnit
    {
        if ($tradeUnitData = $organisationSource->fetchTradeUnit($organisationSourceId)) {
            $baseTradeUnit = null;


            if (TradeUnit::withTrashed()->where('source_slug', $tradeUnitData['trade_unit']['source_slug'])->exists()) {
                if ($tradeUnit = TradeUnit::withTrashed()->where('source_id', $tradeUnitData['trade_unit']['source_id'])->first()) {
                    $tradeUnit = UpdateTradeUnit::make()->action(
                        tradeUnit: $tradeUnit,
                        modelData: $tradeUnitData['trade_unit'],
                    );
                }
                $baseTradeUnit = TradeUnit::withTrashed()->where('source_slug', $tradeUnitData['trade_unit']['source_slug'])->first();
            } else {
                $tradeUnit = StoreTradeUnit::make()->action(
                    group: $organisationSource->getOrganisation()->group,
                    modelData: $tradeUnitData['trade_unit'],
                    hydratorDelay: 30
                );
            }


            if ($tradeUnit) {
                return $tradeUnit;
            }

            return $baseTradeUnit;
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
