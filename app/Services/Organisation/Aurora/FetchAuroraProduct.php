<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:14:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Enums\Market\Product\ProductTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraProduct extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Type'} != 'Product') {
            return;
        }

        $this->parsedData['shop']   = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Product Store Key'});
        $this->parsedData['parent'] = $this->parsedData['shop'];
        if ($this->auroraModelData->{'Product Family Category Key'}) {
            $family = $this->parseFamily($this->organisation->id.':'.$this->auroraModelData->{'Product Family Category Key'});
            if ($family) {
                $this->parsedData['parent'] =$family;
            }
        }

        if ($this->auroraModelData->{'Product Customer Key'}) {
            $customer = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Product Customer Key'});

            $owner_type = 'Customer';
            $owner_id   = $customer->id;
        } else {
            $owner_type = 'Shop';
            $owner_id   = $this->parsedData['shop']->id;
        }


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
            'owner_type'            => $owner_type,
            'owner_id'              => $owner_id,
            'code'                  => $code,
            'name'                  => $this->auroraModelData->{'Product Name'},
            'price'                 => round($unit_price, 2),
            'units'                 => round($units, 3),
            'status'                => $status,
            'state'                 => $state,
            'data'                  => $data,
            'settings'              => $settings,
            'created_at'            => $created_at,
            'trade_unit_composition'=> ProductUnitRelationshipType::MATCH->value,
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
