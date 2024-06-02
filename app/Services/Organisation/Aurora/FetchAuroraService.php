<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:23:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraService extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Type'} != 'Service') {
            return;
        }

        $this->parsedData['shop'] = $this->parseShop(
            $this->organisation->id.':'.$this->auroraModelData->{'Product Store Key'}
        );

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

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});

        $type = AssetTypeEnum::SERVICE;
        if (preg_match('/rent/i', $code)) {
            $type = AssetTypeEnum::RENTAL;
        }

        $this->parsedData['type'] = $type;

        if ($type == AssetTypeEnum::SERVICE) {
            $state = match ($this->auroraModelData->{'Product Status'}) {
                'InProcess' => ServiceStateEnum::IN_PROCESS,
                'Discontinued', 'Discontinuing' => ServiceStateEnum::DISCONTINUED,
                default => ServiceStateEnum::ACTIVE
            };
        } else {
            $state = match ($this->auroraModelData->{'Product Status'}) {
                'InProcess' => RentalStateEnum::IN_PROCESS,
                'Discontinued', 'Discontinuing' => RentalStateEnum::DISCONTINUED,
                default => RentalStateEnum::ACTIVE
            };
        }

        $status = false;
        if ($state == AssetStateEnum::ACTIVE) {
            $status = true;
        }

        $this->parsedData['service'] = [
            'type'                 => $type,
            'owner_type'           => $owner_type,
            'owner_id'             => $owner_id,
            'state'                => $state,
            'code'                 => $code,
            'name'                 => $this->auroraModelData->{'Product Name'},
            'price'                => round($unit_price, 2),
            'status'               => $status,
            'data'                 => $data,
            'settings'             => $settings,
            'created_at'           => $created_at,
            'source_id'            => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},
            'historic_source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Product Current Key'},
        ];


        if ($type == AssetTypeEnum::RENTAL) {
            $autoAssignAsset = match ($code) {
                'Rent-01', 'Rent-02', 'Rent-04' => 'Pallet',
                default => null
            };

            $autoAssignAssetType = match ($code) {
                'Rent-01' => PalletTypeEnum::PALLET->value,
                'Rent-02' => PalletTypeEnum::OVERSIZE->value,
                'Rent-04' => PalletTypeEnum::BOX->value,
                default   => null
            };

            $unit = match ($code) {
                'Rent-06' => RentalUnitEnum::WEEK->value,
                'Rent-05' => RentalUnitEnum::MONTH->value,
                default   => RentalUnitEnum::DAY->value
            };


            $this->parsedData['service']['unit']                   = $unit;
            $this->parsedData['service']['auto_assign_asset']      = $autoAssignAsset;
            $this->parsedData['service']['auto_assign_asset_type'] = $autoAssignAssetType;
        } else {
            $this->parsedData['service']['unit']    = 'service';
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product ID', $id)->first();
    }

}
