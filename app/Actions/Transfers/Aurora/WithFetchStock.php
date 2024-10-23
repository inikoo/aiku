<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Oct 2024 19:02:15 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Inventory\OrgStock\StoreOrgStock;
use App\Actions\Inventory\OrgStock\SyncOrgStockLocations;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\Stock;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Arr;

trait WithFetchStock
{
    protected function processOrgStock(SourceOrganisationService $organisationSource, Stock $effectiveStock, array $stockData): void
    {
        $organisation = $organisationSource->getOrganisation();
        /** @var OrgStock $orgStock */
        if ($orgStock = $organisation->orgStocks()->where('source_id', $stockData['stock']['source_id'])->first()) {
            $orgStock = UpdateOrgStock::make()->action(
                orgStock: $orgStock,
                modelData: $stockData['org_stock'],
                hydratorsDelay: 30
            );
        } else {
            $orgParent = null;

            if ($effectiveStock->stockFamily) {
                $orgParent = $effectiveStock->stockFamily->orgStockFamilies()->where('organisation_id', $organisation->id)->first();
            }

            if (!$orgParent) {
                $orgParent = $organisationSource->getOrganisation();
            }

            $orgStock = StoreOrgStock::make()->action(
                parent: $orgParent,
                stock: $effectiveStock,
                modelData: $stockData['org_stock'],
                hydratorsDelay: 30
            );
        }

        $locationsData = $this->getStockLocationData($organisationSource, $stockData['stock']['source_id']);
        SyncOrgStockLocations::make()->action($orgStock, [
            'locationsData' => $locationsData
        ], 60, false);
    }

    public function updateStockSources(Stock $stock, string $source): void
    {
        $sources   = Arr::get($stock->sources, 'stocks', []);
        $sources[] = $source;
        $sources   = array_unique($sources);

        $stock->updateQuietly([
            'sources' => [
                'stocks' => $sources,
            ]
        ]);
    }

}
