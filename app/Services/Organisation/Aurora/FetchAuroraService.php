<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:23:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Product\ProductTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraService extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Type'} != 'Service') {
            return;
        }

        $this->parsedData['shop'] = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Product Store Key'});

        $data     = [];
        $settings = [];

        $status = 1;
        if ($this->auroraModelData->{'Product Status'} == 'Discontinued') {
            $status = 0;
        }


        if ($this->auroraModelData->{'Product Valid From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $this->auroraModelData->{'Product Valid From'};
        }

        $unit_price = $this->auroraModelData->{'Product Price'};


        $this->parsedData['historic_service_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $this->parsedData['service'] = [
            'type'       => ProductTypeEnum::SERVICE,
            'code'       => $this->auroraModelData->{'Product Code'},
            'name'       => $this->auroraModelData->{'Product Name'},
            'price'      => round($unit_price, 2),
            'status'     => $status,
            'data'       => $data,
            'settings'   => $settings,
            'created_at' => $created_at,
            'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }
}
