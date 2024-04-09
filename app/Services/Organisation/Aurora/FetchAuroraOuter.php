<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Outer\OuterStateEnum;
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


        $product   = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'variant_parent_id'});

        $mainProductData=$this->fetchMainProductData($this->auroraModelData->{'variant_parent_id'});


        $state = match ($this->auroraModelData->{'Product Status'}) {
            'InProcess'     => OuterStateEnum::IN_PROCESS,
            'Discontinuing' => OuterStateEnum::DISCONTINUING,
            'Discontinued'  => OuterStateEnum::DISCONTINUED,
            default         => OuterStateEnum::ACTIVE
        };


        $units = $this->auroraModelData->{'Product Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }


        $main_outer_ratio=$units/$mainProductData->{'Product Units Per Case'};




        $created_at=$this->parseDatetime($this->auroraModelData->{'Product Valid From'});
        if(!$created_at) {
            $created_at=$this->parseDatetime($this->auroraModelData->{'Product For Sale Since Date'});
        }
        if(!$created_at) {
            $created_at=$this->parseDatetime($this->auroraModelData->{'Product First Sold Date'});
        }

        $unit_price        = $this->auroraModelData->{'Product Price'} / $units;


        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});

        $this->parsedData['product']=$product;

        $this->parsedData['outer'] = [
            'code'                  => $code,
            'main_outer_ratio'      => $main_outer_ratio,
            'price'                 => round($unit_price, 2),
            'name'                  => $this->auroraModelData->{'Product Name'},
            'state'                 => $state,
            'created_at'            => $created_at,
            'source_id'             => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},
            'is_main'               => false
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
