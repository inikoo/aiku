<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\SupplyChain\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Actions\SupplyChain\HistoricSupplierProduct\UpdateHistoricSupplierProduct;
use App\Models\SupplyChain\HistoricSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraHistoricSupplierProducts
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricSupplierProduct
    {
        $historicProductData = $organisationSource->fetchHistoricSupplierProduct($source_id);
        if (!$historicProductData) {
            return null;
        }

        $supplierProduct = $historicProductData['supplier_product'];
        $sourceId        = $historicProductData['historic_supplier_product']['source_id'];

        $historicSupplierProduct = $this->findOwnHistoric($supplierProduct, $sourceId, $historicProductData['historic_supplier_product']);

        if ($historicSupplierProduct?->source_id === $sourceId) {
            $historicSupplierProduct = UpdateHistoricSupplierProduct::make()->action(
                historicSupplierProduct: $historicSupplierProduct,
                modelData: $historicProductData['historic_supplier_product'],
                hydratorsDelay: 5,
                strict: false
            );
        }

        if (!$historicSupplierProduct) {
            $sourceIdTaken = HistoricSupplierProduct::where('source_id', $sourceId)->exists();

            $historicSupplierProduct = StoreHistoricSupplierProduct::make()->action(
                supplierProduct: $supplierProduct,
                modelData: $sourceIdTaken ? Arr::except($historicProductData['historic_supplier_product'], 'source_id') : $historicProductData['historic_supplier_product'],
                hydratorsDelay: 5,
                strict: false
            );
        }


        if ($historicSupplierProduct) {
            $this->updateHistoricSupplierProductSources($historicSupplierProduct, $historicProductData['historic_supplier_product']['source_id']);

            if ($historicSupplierProduct->source_id) {
                $sourceData = explode(':', $historicSupplierProduct->source_id);
                DB::connection('aurora')->table('Supplier Part Historic Dimension')
                    ->where('Supplier Part Historic Key', $sourceData[1])
                    ->update(['aiku_supplier_historic_product_id' => $historicSupplierProduct->id]);
            }
        }


        return $historicSupplierProduct;
    }

    /**
     * An Aurora supplier part historic follows its part when the part moves to another supplier, so the
     * same source can already belong to the supplier product the part used to be: only a historic of this
     * supplier product is reused, otherwise purchase order lines would be priced on the old product.
     */
    public function findOwnHistoric(SupplierProduct $supplierProduct, string $sourceId, array $historicData): ?HistoricSupplierProduct
    {
        $ownHistorics = HistoricSupplierProduct::where('supplier_product_id', $supplierProduct->id);

        return (clone $ownHistorics)->where('source_id', $sourceId)->first()
            ?? (clone $ownHistorics)->whereJsonContains('sources->historic_supplier_parts', $sourceId)->first()
            ?? (clone $ownHistorics)
                ->where('code', $historicData['code'])
                ->where('cbm', Arr::get($historicData, 'cbm'))
                ->where('units_per_pack', $historicData['units_per_pack'])
                ->where('units_per_carton', $historicData['units_per_carton'])
                ->first();
    }

    public function updateHistoricSupplierProductSources(HistoricSupplierProduct $historicSupplierProduct, string $source): void
    {
        $sources   = Arr::get($historicSupplierProduct->sources, 'historic_supplier_parts', []);
        $sources[] = $source;
        $sources   = array_unique($sources);

        $historicSupplierProduct->updateQuietly([
            'sources' => [
                'historic_supplier_parts' => $sources,
            ]
        ]);
    }
}
