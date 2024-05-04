<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:36:34 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\OrgSupplierProducts\StoreOrgSupplierProduct;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\SyncSupplierProductTradeUnits;
use App\Actions\Procurement\SupplierProduct\UpdateSupplierProduct;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraSupplierProducts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:supplier-products {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new}  {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?SupplierProduct
    {

        $supplierProductData = $organisationSource->fetchSupplierProduct($organisationSourceId);


        $supplierProduct= $this->fetchSupplierProduct($supplierProductData, $organisationSource);


        if($supplierProduct) {
            $supplierProduct->refresh();
            $organisation = $organisationSource->getOrganisation();

            $orgSupplierProduct = OrgSupplierProduct::where('organisation_id', $organisation->id)->where('supplier_product_id', $supplierProduct->id)->first();
            if(!$orgSupplierProduct) {
                StoreOrgSupplierProduct::make()->run(
                    organisation: $organisation,
                    supplierProduct: $supplierProduct,
                    modelData: [
                        'source_id' => $supplierProductData['supplierProduct']['source_id']
                    ]
                );
            }


        }


        return $supplierProduct;

    }


    public function fetchSupplierProduct($supplierProductData, $organisationSource)
    {




        if ($supplierProductData) {


            $found           = false;
            $supplierProduct = null;
            if ($baseSupplierProduct = SupplierProduct::withTrashed()
                ->where(
                    'source_slug',
                    $supplierProductData['supplierProduct']['source_slug']
                )
                ->first()) {
                $found = true;



                if ($supplierProduct = SupplierProduct::withTrashed()
                    ->where('source_id', $supplierProductData['supplierProduct']['source_id'])
                    ->first()) {
                    $supplierProduct = UpdateSupplierProduct::make()->action(
                        supplierProduct: $supplierProduct,
                        modelData: $supplierProductData['supplierProduct'],
                        skipHistoric: true,
                        hydratorsDelay: $this->hydrateDelay
                    );
                    $this->recordChange($organisationSource, $supplierProduct->wasChanged());

                }


                if (!$supplierProduct) {
                    $sourceData = explode(':', $baseSupplierProduct->source_id);
                    if ($sourceData[0] == $organisationSource->getOrganisation()->id) {
                        // print_r($supplierProductData['supplierProduct']);
                        // print_r($baseSupplierProduct);
                        dd("Error supplier product has same code in same org");
                    }
                }
            }

            if (!$found) {
                if ($baseSupplierProduct = SupplierProduct::withTrashed()
                    ->where(
                        'source_slug',
                        $supplierProductData['supplierProduct']['source_slug_inter_org']
                    )
                    ->where('source_organisation_id', '!=', $organisationSource->getOrganisation()->id)
                    ->first()) {
                    $found = true;
                }
            }



            if (!$found) {
                $supplierProductData['supplierProduct']['source_organisation_id'] = $organisationSource->getOrganisation()->id;
                try {

                    $supplierProduct = StoreSupplierProduct::make()->action(
                        supplier: $supplierProductData['supplier'],
                        modelData: $supplierProductData['supplierProduct'],
                        skipHistoric: true,
                        hydratorsDelay: $this->hydrateDelay
                    );
                    $this->recordNew($organisationSource);


                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $supplierProductData['supplierProduct'], 'SupplierProduct');
                    return null;
                }
            }


            $tradeUnit = $supplierProductData['trade_unit'];


            if ($supplierProduct) {
                SyncSupplierProductTradeUnits::run($supplierProduct, [
                    $tradeUnit->id => [
                        'package_quantity' => $supplierProductData['supplierProduct']['units_per_pack']
                    ]
                ]);

                $sourceData = explode(':', $supplierProduct->source_id);
                DB::connection('aurora')->table('Supplier Part Dimension')
                    ->where('Supplier Part Key', $sourceData[1])
                    ->update(['aiku_id' => $supplierProduct->id]);

                return $supplierProduct;
            } else {
                $sourceData = explode(':', $baseSupplierProduct->source_id);
                DB::connection('aurora')->table('Supplier Part Dimension')
                    ->where('Supplier Part Key', $sourceData[1])
                    ->update(['aiku_id' => $baseSupplierProduct->id]);

                return $baseSupplierProduct;
            }
        }

        return null;

    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->leftJoin('Part Dimension', 'Part SKU', 'Supplier Part Part SKU')
            ->leftJoin('Supplier Dimension as sd', 'Supplier Key', 'Supplier Part Supplier Key')
            ->select('Supplier Part Key as source_id');
        if ($this->onlyNew) {
            $query->whereNull('spp.aiku_id');
        }

        return $query->where('Supplier Part Status', ['Available', 'NoAvailable'])
            ->where('Part Status', '!=', 'Not In Use')
            ->where('spp.aiku_ignore', 'No')
            ->where('sd.aiku_ignore', 'No')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->leftJoin('Part Dimension', 'Part SKU', 'Supplier Part Part SKU')
            ->leftJoin('Supplier Dimension as sd', 'Supplier Key', 'Supplier Part Supplier Key')
            ->select('Supplier Part Key as source_id');
        if ($this->onlyNew) {
            $query->whereNull('spp.aiku_id');
        }

        return $query->where('Supplier Part Status', ['Available', 'NoAvailable'])
            ->where('Part Status', '!=', 'Not In Use')
            ->where('spp.aiku_ignore', 'No')
            ->where('sd.aiku_ignore', 'No')
            ->count();
    }
}
