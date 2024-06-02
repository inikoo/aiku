<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Catalogue\Service\StoreService;
use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Fulfilment\Rental\UpdateRental;
use App\Actions\Catalogue\Service\UpdateService;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Fulfilment\Rental;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Service;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraServices extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:services {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Asset
    {

        if ($serviceData = $organisationSource->fetchService($organisationSourceId)) {

            if($serviceData['type']==AssetTypeEnum::SERVICE) {

                if ($service = Service::where('source_id', $serviceData['service']['source_id'])
                    ->first()) {
                    $service = UpdateService::make()->action(
                        service:      $service,
                        modelData:    $serviceData['service'],
                    );
                } else {
                    try {

                        $service = StoreService::make()->action(
                            shop:         $serviceData['shop'],
                            modelData:    $serviceData['service'],
                        );
                    } catch (Exception $e) {
                        dd($e->getMessage());
                        $this->recordError($organisationSource, $e, $serviceData['service'], 'Asset', 'store');
                        return null;
                    }
                }
                $sourceData = explode(':', $service->source_id);

                DB::connection('aurora')->table('Product Dimension')
                    ->where('Product ID', $sourceData[1])
                    ->update(['aiku_id' =>$service->asset->id]);
                return $service->asset;

            } else {

                if ($rental = Rental::where('source_id', $serviceData['service']['source_id'])
                    ->first()) {
                    $rental = UpdateRental::make()->action(
                        rental:      $rental,
                        modelData:    $serviceData['service'],
                    );
                } else {
                    try {

                        $rental = StoreRental::make()->action(
                            shop:         $serviceData['shop'],
                            modelData:    $serviceData['service'],
                        );
                    } catch (Exception $e) {
                        dd($e->getMessage());
                        $this->recordError($organisationSource, $e, $serviceData['service'], 'Asset', 'store');
                        return null;
                    }
                }
                $sourceData = explode(':', $rental->source_id);


                DB::connection('aurora')->table('Product Dimension')
                    ->where('Product ID', $sourceData[1])
                    ->update(['aiku_id' =>$rental->asset_id]);
                return $rental->asset;

            }




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

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
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

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query->count();
    }
}
