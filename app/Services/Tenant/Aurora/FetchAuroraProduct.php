<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:14:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

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

        $this->parsedData['shop']   = $this->parseShop($this->auroraModelData->{'Product Store Key'});
        $this->parsedData['parent'] = $this->parsedData['shop'];
        if ($this->auroraModelData->{'Product Family Category Key'}) {
            $family = $this->parseFamily($this->auroraModelData->{'Product Family Category Key'});
            if ($family) {
                $this->parsedData['parent'] =$family;
            }
        }

        if ($this->auroraModelData->{'Product Customer Key'}) {
            $customer = $this->parseCustomer($this->auroraModelData->{'Product Customer Key'});

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
            'InProcess'     => 'in-process',
            'Discontinuing' => 'discontinuing',
            'Discontinued'  => 'discontinued',
            default         => 'active',
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

        $this->parsedData['historic_product_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $this->parsedData['product'] = [
            'type'       => ProductTypeEnum::PHYSICAL_GOOD,
            'owner_type' => $owner_type,
            'owner_id'   => $owner_id,
            'code'       => $this->auroraModelData->{'Product Code'},
            'name'       => $this->auroraModelData->{'Product Name'},
            'price'      => round($unit_price, 2),
            'units'      => round($units, 3),
            'status'     => $status,
            'state'      => $state,
            'data'       => $data,
            'settings'   => $settings,
            'created_at' => $created_at,
            'source_id'  => $this->auroraModelData->{'Product ID'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }
}
