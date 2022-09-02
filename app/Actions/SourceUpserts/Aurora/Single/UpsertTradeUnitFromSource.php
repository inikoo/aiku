<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:42:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Marketing\TradeUnit\StoreTradeUnit;
use App\Actions\Marketing\TradeUnit\UpdateTradeUnit;
use App\Models\Marketing\TradeUnit;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertTradeUnitFromSource
{
    use AsAction;


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?TradeUnit
    {
        if ($tradeUnitData = $organisationSource->fetchTradeUnit($organisation_source_id)) {
            if ($tradeUnit = TradeUnit::where('organisation_source_id', $tradeUnitData['trade_unit']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateTradeUnit::run(
                    tradeUnit: $tradeUnit,
                    modelData: $tradeUnitData['trade_unit'],
                );
            } else {
                $res = StoreTradeUnit::run(
                    organisation: $organisationSource->organisation,
                    modelData:    $tradeUnitData['trade_unit']
                );
            }


            return $res->model;
        }


        return null;
    }


}
