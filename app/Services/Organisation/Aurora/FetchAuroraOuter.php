<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraOuter extends FetchAurora
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

        // get Product

        $this->parsedData['product']   = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'variant_parent_id'});



        $data     = [];
        $settings = [];

        $status = 1;
        if ($this->auroraModelData->{'Product Status'} == 'Discontinued') {
            $status = 0;
        }

        $state = match ($this->auroraModelData->{'Product Status'}) {
            'InProcess'     => ProductStateEnum::IN_PROCESS,
            'Discontinuing' => ProductStateEnum::DISCONTINUING,
            'Discontinued'  => ProductStateEnum::DISCONTINUED,
            default         => ProductStateEnum::ACTIVE
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

        $this->parsedData['historic_outer_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});


        $this->parsedData['product'] = [
            'type'                  => ProductTypeEnum::PHYSICAL_GOOD,

            'code'                  => $code,
            'name'                  => $this->auroraModelData->{'Product Name'},
            'price'                 => round($unit_price, 2),
            'units'                 => round($units, 3),
            'status'                => $status,
            'state'                 => $state,
            'data'                  => $data,
            'settings'              => $settings,
            'created_at'            => $created_at,
            'source_id'             => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }
}
