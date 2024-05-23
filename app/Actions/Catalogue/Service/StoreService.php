<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:42:18 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service;

use App\Actions\Catalogue\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateHistoricOuterables;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use Illuminate\Validation\Rule;

class StoreService extends OrgAction
{
    public function handle(Product $product, array $modelData): Service
    {


        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'product_id', $product->id);


        $service=Service::create($modelData);

        $product->update(
            [
                'main_outerable_id'=> $service->id
            ]
        );


        $service->salesIntervals()->create();


        $historicOuterable = StoreHistoricOuterable::run(
            $service,
            [
                'source_id'=> $service->historic_source_id
            ]
        );
        $product->update(
            [
                'current_historic_outerable_id' => $historicOuterable->id,
            ]
        );

        ProductHydrateHistoricOuterables::dispatch($product);

        return $service;
    }

    public function rules(): array
    {
        return [
            'status'                  => ['required', 'boolean'],
            'state'                   => ['required', Rule::enum(ServiceStateEnum::class)],
            'data'                    => ['sometimes', 'array'],
            'created_at'              => ['sometimes', 'date'],
            'source_id'               => ['sometimes','string','max:63'],
            'auto_assign_action'      => ['nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_action_type' => ['nullable', 'string', 'in:pallet,box,oversize'],

            'price'                  => ['required', 'numeric', 'min:0'],
            'unit'                   => ['sometimes','nullable', 'string'],
        ];

    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Service
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }




}
