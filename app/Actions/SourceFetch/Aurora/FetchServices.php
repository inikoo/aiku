<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\Product\StoreNoPhysicalGood;
use App\Actions\Market\Product\UpdateProduct;
use App\Models\Market\Product;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\Organisation\SourceOrganisationService;

class FetchServices extends FetchAction
{
    public string $commandSignature = 'fetch:services {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {

        if ($serviceData = $organisationSource->fetchService($organisationSourceId)) {

            if ($service = Product::where('source_id', $serviceData['service']['source_id'])
                ->first()) {
                $service = UpdateProduct::make()->action(
                    product:      $service,
                    modelData:    $serviceData['service'],
                    skipHistoric: true
                );
            } else {
                try {

                    $service = StoreNoPhysicalGood::make()->action(
                        parent:         $serviceData['shop'],
                        modelData:    $serviceData['service'],
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $serviceData['service'], 'Product', 'store');
                    return null;
                }
            }

            $sourceData = explode(':', $service->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $service->id]);



            return $service;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Service')
            ->select('Product ID as source_id')
            ->orderBy('Product ID');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        $sourceData = explode(':', $this->shop->source_id);
        if ($this->shop) {
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Product Dimension')
            ->where('Product Type', 'Service');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        $sourceData = explode(':', $this->shop->source_id);
        if ($this->shop) {
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query->count();
    }
}
