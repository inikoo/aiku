<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 17:10:20 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;

trait IsStoreProduct
{
    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, $strict = true): Asset
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->state          = Arr::get($modelData, 'state');
        $this->strict         = $strict;
        $this->parent         = $parent;

        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    private function prepareProductForValidation(): void
    {
        if ($this->parent instanceof ProductCategory) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->shop_id
                ]
            );
        } elseif ($this->parent instanceof Shop) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->id
                ]
            );
        }

        if (!$this->has('status')) {
            $this->set('status', true);
        }
    }

    private function getProductRules(): array
    {
        $rules = [
            'code'                 => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'assets',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => AssetStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'family_id'            => ['sometimes', 'required', 'exists:families,id'],
            'image_id'             => ['sometimes', 'required', 'exists:media,id'],
            'price'                => ['required', 'numeric', 'min:0'],
            'unit'                 => ['sometimes', 'required', 'string'],
            'rrp'                  => ['sometimes', 'required', 'numeric', 'min:0'],
            'name'                 => ['required', 'max:250', 'string'],
            'description'          => ['sometimes', 'required', 'max:1500'],
            'source_id'            => ['sometimes', 'required', 'string', 'max:255'],
            'historic_source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'owner_id'             => 'required',
            'owner_type'           => 'required',
            'status'               => ['required', 'boolean'],
            'data'                 => ['sometimes', 'array'],
            'settings'             => ['sometimes', 'array'],
            'created_at'           => ['sometimes', 'date'],

        ];

        if ($this->state == AssetStateEnum::DISCONTINUED) {
            $rules['code'] = [
                'required',
                'max:32',
                'alpha_dash',
            ];
        }


        return $rules;
    }

    private function setDataFromParent(Shop|ProductCategory $parent, array $modelData): array
    {
        if (class_basename($parent) == 'Shop') {
            $modelData['shop_id']     = $parent->id;
            $modelData['parent_id']   = $parent->id;
            $modelData['parent_type'] = $parent->type;
            $modelData['owner_id']    = $parent->id;
            $modelData['owner_type']  = $parent->type;
            $modelData['currency_id'] = $parent->currency_id;
        } else {
            $modelData['shop_id']     = $parent->shop_id;
            $modelData['owner_id']    = $parent->parent_id;
            $modelData['owner_type']  = $parent->shop->type;
            $modelData['currency_id'] = $parent->shop->currency_id;
        }

        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);

        return $modelData;
    }


}
