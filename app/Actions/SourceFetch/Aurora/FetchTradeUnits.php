<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:28:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Marketing\TradeUnit\StoreTradeUnit;
use App\Actions\Marketing\TradeUnit\UpdateTradeUnit;
use App\Models\Marketing\TradeUnit;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchTradeUnits
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $source_id): ?TradeUnit
    {
        if ($tradeUnitData = $tenantSource->fetchTradeUnit($source_id)) {
            if ($tradeUnit = TradeUnit::where('source_id', $tradeUnitData['trade_unit']['source_id'])
                ->first()) {
                $tradeUnit = UpdateTradeUnit::run(
                    tradeUnit: $tradeUnit,
                    modelData: $tradeUnitData['trade_unit'],
                );
            } else {
                $tradeUnit = StoreTradeUnit::run(
                    modelData:    $tradeUnitData['trade_unit']
                );
            }


            return $tradeUnit;
        }


        return null;
    }


}
