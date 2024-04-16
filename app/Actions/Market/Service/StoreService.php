<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:42:18 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Service;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\Product\Hydrators\ProductHydrateHistoricOuterables;
use App\Actions\OrgAction;
use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Market\Product;
use App\Models\Market\Service;
use Illuminate\Validation\Rule;

class StoreService extends OrgAction
{
    private bool |null $state=null;

    public function handle(Product $product, array $modelData): Service
    {


        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelDaiva, 'state', $product->state);
        data_set($modelData, 'product_id', $product->id);


        $service=Service::create($modelData);

        $product->update(
            [
                'main_outerable_id'=> $service->id
            ]
        );


        $service->salesStats()->create();


        $historicOuterable = StoreHistoricOuterable::run($service);
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

         //   'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'state'       => ['required', Rule::enum(ServiceStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],
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
