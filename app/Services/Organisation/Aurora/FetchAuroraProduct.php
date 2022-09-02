<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:14:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraProduct extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Product Store Key'});

        $data     = [];
        $settings = [];

        $status = 1;
        if ($this->auroraModelData->{'Product Status'} == 'Discontinued') {
            $status = 0;
        }

        $state = match ($this->auroraModelData->{'Product Status'}) {
            'InProcess' => 'creating',
            'Discontinuing' => 'discontinuing',
            'Discontinued' => 'discontinued',
            default => 'active',
        };


        $units = $this->auroraModelData->{'Product Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        if ($this->auroraModelData->{'Product Valid From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $this->auroraModelData->{'Product Valid From'};
        }

        $unit_price        = $this->auroraModelData->{'Product Price'} / $units;
        $data['raw_price'] = $unit_price;


        $this->parsedData['product'] = [
            'code' => $this->auroraModelData->{'Product Code'},
            'name' => $this->auroraModelData->{'Product Name'},
            'price' => round($unit_price, 2),
            'outer' => round($units, 3),
            'status' => $status,
            'state'  => $state,
            'data'       => $data,
            'settings'   => $settings,
            'created_at' => $created_at,
            'organisation_source_id' => $this->auroraModelData->{'Product ID'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }

}
