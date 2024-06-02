<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 03:04:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraTradeUnitImages extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
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
                'Asset',
                $auroraProductsData->{'Product Part Product ID'}
            )->map(function ($auroraImage) {
                return $this->fetchImage($auroraImage);
            })->all();

            $images = array_merge($images, $productImages);
        }


        $this->parsedData['images'] = $images;

        $this->parsedData['trade_unit'] = [
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Part SKU'},
        ];


    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU')
            ->where('Part SKU', $id)->first();
    }
}
