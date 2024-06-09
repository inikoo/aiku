<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Transfers\Aurora\FetchAurora;
use App\Transfers\Aurora\WithAuroraImages;
use Illuminate\Support\Facades\DB;

class FetchAuroraVariant extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Type'} != 'Product') {
            return;
        }

        if ($this->auroraModelData->{'is_variant'} != 'Yes') {
            return;
        }



        $product = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'variant_parent_id'});

        if(!$product) {
            return;
        }

        $mainProductData = $this->fetchMainProductData($this->auroraModelData->{'variant_parent_id'});



        $units = $this->auroraModelData->{'Product Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }


        $ratio = $units / $mainProductData->{'Product Units Per Case'};


        $created_at = $this->parseDatetime($this->auroraModelData->{'Product Valid From'});
        if (!$created_at) {
            $created_at = $this->parseDatetime($this->auroraModelData->{'Product For Sale Since Date'});
        }
        if (!$created_at) {
            $created_at = $this->parseDatetime($this->auroraModelData->{'Product First Sold Date'});
        }

        $unit_price = $this->auroraModelData->{'Product Price'} / $units;


        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});

        $this->parsedData['product'] = $product;

        $this->parsedData['variant'] = [
            'code'       => $code,
            'ratio'      => $ratio,
            'price'      => round($unit_price, 2),
            'name'       => $this->auroraModelData->{'Product Name'},
            'created_at' => $created_at,
            'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},
            'is_main'    => false,
            'is_visible' => $this->auroraModelData->{'Product Show Variant'} == 'Yes',
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }

    protected function fetchMainProductData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }

}
