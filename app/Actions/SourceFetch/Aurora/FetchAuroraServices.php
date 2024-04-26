<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\Product\StoreRentalProduct;
use App\Actions\Market\Product\StoreServiceProduct;
use App\Actions\Market\Rental\UpdateRental;
use App\Actions\Market\Service\UpdateService;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Market\Product;
use App\Models\Market\Rental;
use App\Models\Market\Service;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\Organisation\SourceOrganisationService;

class FetchAuroraServices extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:services {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {

        if ($serviceData = $organisationSource->fetchService($organisationSourceId)) {

            if($serviceData['type']==ProductTypeEnum::SERVICE) {

                if ($service = Service::where('source_id', $serviceData['service']['source_id'])
                    ->first()) {
                    $service = UpdateService::make()->action(
                        service:      $service,
                        modelData:    $serviceData['service'],
                    );
                    $serviceProduct=$service->product;
                } else {
                    try {

                        $serviceProduct = StoreServiceProduct::make()->action(
                            parent:         $serviceData['shop'],
                            modelData:    $serviceData['service'],
                        );
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $serviceData['service'], 'Product', 'store');
                        return null;
                    }
                }
                $sourceData = explode(':', $serviceProduct->source_id);

                DB::connection('aurora')->table('Product Dimension')
                    ->where('Product ID', $sourceData[1])
                    ->update(['aiku_id' =>$serviceProduct->main_outerable_id]);
                return $serviceProduct;

            } else {

                if ($rental = Rental::where('source_id', $serviceData['service']['source_id'])
                    ->first()) {
                    $rental = UpdateRental::make()->action(
                        rental:      $rental,
                        modelData:    $serviceData['service'],
                    );
                    $rentalProduct=$rental->product;
                } else {
                    try {

                        $rentalProduct = StoreRentalProduct::make()->action(
                            parent:         $serviceData['shop'],
                            modelData:    $serviceData['service'],
                        );
                    } catch (Exception $e) {
                        dd($e);
                        $this->recordError($organisationSource, $e, $serviceData['service'], 'Product', 'store');
                        return null;
                    }
                }
                $sourceData = explode(':', $rentalProduct->source_id);


                DB::connection('aurora')->table('Product Dimension')
                    ->where('Product ID', $sourceData[1])
                    ->update(['aiku_id' =>$rentalProduct->main_outerable_id]);
                return $rentalProduct;

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
