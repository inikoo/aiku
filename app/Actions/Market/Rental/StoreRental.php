<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 17:23:23 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Rental;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\Product\Hydrators\ProductHydrateHistoricOuterables;
use App\Actions\OrgAction;
use App\Enums\Market\Rental\RentalStateEnum;
use App\Models\Market\Product;
use App\Models\Market\Rental;
use Illuminate\Validation\Rule;

class StoreRental extends OrgAction
{
    public function handle(Product $product, array $modelData): Rental
    {

        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'fulfilment_id', $product->shop->fulfilment->id);
        data_set($modelData, 'product_id', $product->id);

        $rental=Rental::create($modelData);

        $product->update(
            [
                'main_outerable_id'=> $rental->id
            ]
        );


        $rental->salesStats()->create();


        $historicOuterable = StoreHistoricOuterable::run(
            $rental,
            [
                    'source_id'=> $rental->historic_source_id
            ]
        );
        $product->update(
            [
                'current_historic_outerable_id' => $historicOuterable->id,
            ]
        );

        ProductHydrateHistoricOuterables::dispatch($product);

        return $rental;
    }

    public function rules(): array
    {
        return [
            'status'      => ['required', 'boolean'],
            'state'       => ['required', Rule::enum(RentalStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],
            'source_id'   => ['sometimes','string','max:63']

        ];

    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;

        $this->initialisationFromShop($product->shop, $modelData);
        return $this->handle($product, $this->validatedData);
    }




}
