<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\Product\Hydrators\ProductHydrateHistoricOuterables;
use App\Actions\Market\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\Market\Product;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreRental extends OrgAction
{
    public function handle(array $modelData): Rental
    {
        $productId = Arr::get($modelData, 'product_id');

        if($productId) {
            $product = Product::find($productId);

            data_set($modelData, 'organisation_id', $product->organisation_id);
            data_set($modelData, 'group_id', $product->group_id);
            data_set($modelData, 'shop_id', $product->shop_id);
            data_set($modelData, 'fulfilment_id', $product->shop->fulfilment->id);
            data_set($modelData, 'product_id', $product->id);
        }

        $rental = Rental::create($modelData);

        if($productId) {
            $product->update(
                [
                    'main_outerable_id' => $rental->id
                ]
            );
        }

        $rental->salesStats()->create();


        $historicOuterable = StoreHistoricOuterable::run(
            $rental,
            [
                'source_id' => $rental->historic_source_id
            ]
        );

        if($productId) {
            $product->update(
                [
                    'current_historic_outerable_id' => $historicOuterable->id,
                ]
            );

            ProductHydrateHistoricOuterables::dispatch($product);
            ShopHydrateRentals::dispatch($product->shop);
        }

        return $rental;
    }

    public function rules(): array
    {
        return [
            'status'                 => ['sometimes', 'boolean'],
            'product_id'             => ['sometimes', 'exists:products,id'],
            'state'                  => ['sometimes', Rule::enum(RentalStateEnum::class)],
            'data'                   => ['sometimes', 'array'],
            'created_at'             => ['sometimes', 'date'],
            'source_id'              => ['sometimes', 'string', 'max:63'],
            'auto_assign_asset'      => ['nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_asset_type' => ['nullable', 'string', 'in:pallet,box,oversize'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'unit'                   => ['required', 'string'],
        ];
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Rental
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;

        $modelData['product_id'] = $product->id;
        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($this->validatedData);
    }
}
