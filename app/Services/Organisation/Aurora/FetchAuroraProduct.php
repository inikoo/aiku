<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:14:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use Illuminate\Support\Facades\DB;

class FetchAuroraProduct extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Type'} != 'Product') {
            return;
        }

        if ($this->auroraModelData->{'is_variant'} != 'No') {
            return;
        }

        $this->parsedData['shop']   = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Product Store Key'});
        $this->parsedData['parent'] = $this->parsedData['shop'];
        if ($this->auroraModelData->{'Product Family Category Key'}) {
            $family = $this->parseFamily($this->organisation->id.':'.$this->auroraModelData->{'Product Family Category Key'});
            if ($family) {
                $this->parsedData['parent'] = $family;
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
            'InProcess'     => AssetStateEnum::IN_PROCESS,
            'Discontinuing' => AssetStateEnum::DISCONTINUING,
            'Discontinued'  => AssetStateEnum::DISCONTINUED,
            default         => AssetStateEnum::ACTIVE
        };


        $units = $this->auroraModelData->{'Product Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        $created_at = $this->parseDatetime($this->auroraModelData->{'Product Valid From'});
        if (!$created_at) {
            $created_at = $this->parseDatetime($this->auroraModelData->{'Product For Sale Since Date'});
        }
        if (!$created_at) {
            $created_at = $this->parseDatetime($this->auroraModelData->{'Product First Sold Date'});
        }

        $unit_price        = $this->auroraModelData->{'Product Price'} / $units;
        $data['raw_price'] = $unit_price;

        $this->parsedData['historic_asset_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});


        $this->parsedData['product'] = [
            'type'                   => AssetTypeEnum::PRODUCT,
            'owner_type'             => $owner_type,
            'owner_id'               => $owner_id,
            'code'                   => $code,
            'name'                   => $this->auroraModelData->{'Product Name'},
            'price'                  => round($unit_price, 2),
            //   'units'                 => round($units, 3),
            'status'                 => $status,
            'state'                  => $state,
            'data'                   => $data,
            'settings'               => $settings,
            'created_at'             => $created_at,
            'trade_unit_composition' => ProductUnitRelationshipType::SINGLE,
            'source_id'              => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},
            'historic_source_id'     => $this->organisation->id.':'.$this->auroraModelData->{'Product Current Key'},
            'images'                 => $this->parseImages()
        ];
    }

    private function parseImages(): array
    {
        $images = $this->getModelImagesCollection(
            'Asset',
            $this->auroraModelData->{'Product ID'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        });

        return $images->toArray();
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }
}
