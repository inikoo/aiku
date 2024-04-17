<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:23:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Product\ProductStateEnum;
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



        if ($this->auroraModelData->{'Product Valid From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $this->auroraModelData->{'Product Valid From'};
        }

        $unit_price = $this->auroraModelData->{'Product Price'};


        $this->parsedData['historic_service_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $owner_type = 'Shop';
        $owner_id   = $this->parsedData['shop']->id;

        $state = match ($this->auroraModelData->{'Product Status'}) {
            'InProcess'     => ProductStateEnum::IN_PROCESS,
            'Discontinued','Discontinuing'  => ProductStateEnum::DISCONTINUED,
            default         => ProductStateEnum::ACTIVE
        };

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});


        $type= ProductTypeEnum::SERVICE;
        if(preg_match('/rent/i', $code)) {
            $type= ProductTypeEnum::RENTAL;
        }

        $status=false;
        if($state==ProductStateEnum::ACTIVE) {
            $status=true;
        }

        $this->parsedData['service'] = [
            'type'                      => $type,
            'owner_type'                => $owner_type,
            'owner_id'                  => $owner_id,
            'state'                     => $state,
            'code'                      => $code,
            'name'                      => $this->auroraModelData->{'Product Name'},
            'main_outerable_price'      => round($unit_price, 2),
            'status'                    => $status,
            'data'                      => $data,
            'settings'                  => $settings,
            'created_at'                => $created_at,
            'source_id'                 => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }
}
