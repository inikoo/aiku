<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Enums\Catalogue\Product\ProductTradeConfigEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
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

        if ($this->parsedData['shop']->type == ShopTypeEnum::FULFILMENT) {
            return;
        }

        $this->parsedData['parent'] = $this->parsedData['shop'];
        if ($this->auroraModelData->{'Product Family Category Key'}) {
            $family = $this->parseFamily($this->organisation->id.':'.$this->auroraModelData->{'Product Family Category Key'});
            if ($family) {
                if ($family->shop_id != $this->parsedData['shop']->id) {
                    dd('Wrong family - shop');
                }
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


        $state = match ($this->auroraModelData->{'Product Status'}) {
            'InProcess' => ProductStateEnum::IN_PROCESS,
            'Discontinuing' => ProductStateEnum::DISCONTINUING,
            'Discontinued' => ProductStateEnum::DISCONTINUED,
            default => ProductStateEnum::ACTIVE
        };


        //enum('Online Force Out of Stock','Online Auto','Offline','Online Force For Sale')

        if ($this->auroraModelData->{'Product Status'} == 'InProcess') {
            $status = ProductStatusEnum::IN_PROCESS;
        } elseif ($this->auroraModelData->{'Product Status'} == 'Discontinued') {
            $status = ProductStatusEnum::DISCONTINUED;
        } elseif ($this->auroraModelData->{'Product Web Configuration'} == 'Offline') {
            $status = ProductStatusEnum::NOT_FOR_SALE;
        } elseif ($this->auroraModelData->{'Product Web Configuration'} == 'Online Force Out of Stock') {
            $status = ProductStatusEnum::OUT_OF_STOCK;
        } elseif ($this->auroraModelData->{'Product Web Configuration'} == 'Online Force For Sale') {
            $status = ProductStatusEnum::FOR_SALE;
        } else {
            //enum('For Sale','Out of Stock','Discontinued','Offline')
            $status = match ($this->auroraModelData->{'Product Web State'}) {
                'Discontinued' => ProductStatusEnum::DISCONTINUED,
                'Offline' => ProductStatusEnum::NOT_FOR_SALE,
                'Out of Stock' => ProductStatusEnum::OUT_OF_STOCK,
                default => ProductStatusEnum::FOR_SALE
            };
        }

        $tradeConfig = match ($this->auroraModelData->{'Product Web Configuration'}) {
            'Online Force For Sale' => ProductTradeConfigEnum::FORCE_FOR_SALE,
            'Online Force Out of Stock' => ProductTradeConfigEnum::FORCE_OUT_OF_STOCK,
            'Offline' => ProductTradeConfigEnum::FORCE_OFFLINE,
            default => ProductTradeConfigEnum::AUTO
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

        $unit_price                  = $this->auroraModelData->{'Product Price'} / $units;
        $data['original_unit_price'] = $unit_price;

        $this->parsedData['historic_asset_source_id'] = $this->auroraModelData->{'Product Current Key'};

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Product Code'});

        $name = $this->auroraModelData->{'Product Name'};
        if (!$name) {
            $name = $code;
        }

        $this->parsedData['product'] = [
            'is_main'                => true,
            'type'                   => AssetTypeEnum::PRODUCT,
            'owner_type'             => $owner_type,
            'owner_id'               => $owner_id,
            'code'                   => $code,
            'name'                   => $name,
            'price'                  => round($unit_price, 2),
            'status'                 => $status,
            'unit'                   => $this->auroraModelData->{'Product Unit Label'},
            'state'                  => $state,
            'trade_config'           => $tradeConfig,
            'data'                   => $data,
            'settings'               => $settings,
            'created_at'             => $created_at,
            'trade_unit_composition' => ProductUnitRelationshipType::SINGLE,
            'source_id'              => $this->organisation->id.':'.$this->auroraModelData->{'Product ID'},
            'historic_source_id'     => $this->organisation->id.':'.$this->auroraModelData->{'Product Current Key'},
            'images'                 => $this->parseImages(),
            'fetched_at'             => now(),
            'last_fetched_at'        => now(),
        ];


        if ($this->auroraModelData->{'is_variant'} == 'Yes') {
            $this->parsedData['product']['is_main'] = false;
            $mainProduct                            = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'variant_parent_id'});


            $this->parsedData['product']['variant_ratio']      = $units / $mainProduct->units;
            $this->parsedData['product']['variant_is_visible'] = $this->auroraModelData->{'Product Show Variant'} == 'Yes';
            $this->parsedData['product']['main_product_id']    = $mainProduct->id;
        }
    }

    private function parseImages(): array
    {
        $images = $this->getModelImagesCollection(
            'Product',
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
