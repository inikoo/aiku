<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:28:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\TradeUnit\StoreTradeUnit;
use App\Actions\Marketing\TradeUnit\UpdateTradeUnit;
use App\Actions\Utils\StoreImage;
use App\Models\Marketing\TradeUnit;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchTradeUnits extends FetchAction
{
    public string $commandSignature = 'fetch:trade-units {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?TradeUnit
    {
        if ($tradeUnitData = $tenantSource->fetchTradeUnit($tenantSourceId)) {
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

            foreach ($tradeUnitData['images'] ?? [] as $imageData) {
                if (isset($imageData['image_path']) and isset($imageData['filename'])) {
                    StoreImage::run($tradeUnit, $imageData['image_path'], $imageData['filename']);
                }
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
