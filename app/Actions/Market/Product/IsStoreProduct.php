<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 17:10:20 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

trait IsStoreProduct
{
    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, bool $skipHistoric = false): Product
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->state          =Arr::get($modelData, 'state');
        $this->parent         =$parent;

        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData, $skipHistoric);
    }

    private function prepareProductForValidation(): void
    {
        if($this->parent instanceof ProductCategory) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->shop_id
                ]
            );
        } elseif($this->parent instanceof Shop) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->id
                ]
            );
        }

        if(!$this->has('status')) {
            $this->set('status', true);
        }

        if(!$this->has('state')) {
            $this->set('state', ProductStateEnum::IN_PROCESS);
        }

    }

    private function getProductRules(): array
    {
        $rules= [
            'code'        => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),
            ],
            'family_id'   => ['sometimes', 'required', 'exists:families,id'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'price'       => ['required', 'numeric','min:0'],
            'rrp'         => ['sometimes', 'required', 'numeric','min:0'],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'type'        => ['required', Rule::enum(ProductTypeEnum::class)],
            'owner_id'    => 'required',
            'owner_type'  => 'required',
            'status'      => ['required', 'boolean'],
            'state'       => ['required', Rule::enum(ProductStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],

        ];

        if($this->state==ProductStateEnum::DISCONTINUED) {
            $rules['code']= [
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
        } else {
            $modelData['shop_id']    = $parent->shop_id;
            $modelData['owner_id']   = $parent->parent_id;
            $modelData['owner_type'] = $parent->shop->type;
        }

        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);
        return $modelData;
    }


}
