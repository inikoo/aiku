<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 01:44:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Models\Inventory\Stock;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertStockFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:stock {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Stock
    {
        if ($stockData = $organisationSource->fetchStock($organisation_source_id)) {
            if ($stock = Stock::where('organisation_source_id', $stockData['stock']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateStock::run(
                    stock:     $stock,
                    modelData: $stockData['stock'],
                );
            } else {
                $res = StoreStock::run(
                    owner: $organisationSource->organisation,
                    modelData:    $stockData['stock']
                );
            }
            $stock=$res->model;
            $tradeUnit = UpsertTradeUnitFromSource::run($organisationSource, $res->model->organisation_source_id);
            $stock->tradeUnits()->sync([
                                           $tradeUnit->id => [
                                               'quantity' => $stockData['units_per_package']
                                           ]
                                       ]);


            return $stock;
        }


        return null;
    }


}
