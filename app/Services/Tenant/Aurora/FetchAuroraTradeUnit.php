<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 03:04:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraTradeUnit extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        $this->parsedData['images'] = $this->parseImages();

        $this->parsedData['trade_unit'] = [
            'name'      => $this->auroraModelData->{'Part Recommended Product Unit Name'},
            'code'      => $this->auroraModelData->{'Part Reference'},
            'source_id' => $this->auroraModelData->{'Part SKU'},
        ];
    }

    private function parseImages(): array
    {
        $images = [];

        $stockImages = $this->getModelImagesCollection(
            'Part',
            $this->auroraModelData->{'Part SKU'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        })->all();

        $images = array_merge($images, $stockImages);

        foreach (
            DB::connection('aurora')
                ->table('Product Part Bridge')
                ->where('Product Part Part SKU', $this->auroraModelData->{'Part SKU'})->get() as $auroraProductsData
        ) {
            $productImages = $this->getModelImagesCollection(
                'Product',
                $auroraProductsData->{'Product Part Product ID'}
            )->map(function ($auroraImage) {
                return $this->fetchImage($auroraImage);
            })->all();

            $images = array_merge($images, $productImages);
        }

        return $images;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }
}
