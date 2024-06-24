<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:30:13 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductVariant;

use App\Actions\Catalogue\HistoricProductVariant\StoreHistoricProductVariant;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateProductVariants;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\ProductVariant\ProductVariantStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreProductVariant extends OrgAction
{
    public function handle(Product $product, array $modelData): ProductVariant
    {
        $tradeUnitsData = [];
        foreach ($product->tradeUnits as $tradeUnit) {
            $tradeUnitsData[] =
                [
                    'tradeUnit' => $tradeUnit,
                    'data'      => [
                        'units' => $tradeUnit->pivot->units * $modelData['ratio'],
                        'notes' => Arr::get($modelData, 'is_main') ? $tradeUnit->pivot->notes : null
                    ]
                ];
        }


        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'asset_id', $product->asset_id);
        data_set($modelData, 'family_id', $product->family_id);
        data_set($modelData, 'department_id', $product->department_id);

        data_set($modelData, 'shop_id', $product->shop_id);


        data_set($modelData, 'currency_id', $product->currency_id);
        data_set($modelData, 'unit', $product->unit);
        data_set($modelData, 'units', $product->units * $modelData['ratio']);

        data_set($modelData, 'status', $product->status);
        data_set(
            $modelData,
            'state',
            match ($product->state) {
                ProductStateEnum::ACTIVE        => ProductVariantStateEnum::ACTIVE,
                ProductStateEnum::DISCONTINUING => ProductVariantStateEnum::DISCONTINUING,
                ProductStateEnum::DISCONTINUED  => ProductVariantStateEnum::DISCONTINUED,
                default                         => ProductVariantStateEnum::IN_PROCESS
            }
        );


        /** @var ProductVariant $productVariant */
        $productVariant = $product->productVariants()->create($modelData);
        $productVariant->stats()->create();
        $productVariant->salesIntervals()->create();
        $productVariant->refresh();

        foreach ($tradeUnitsData as $tradeUnitData) {
            $productVariant->tradeUnits()->attach(
                $tradeUnitData['tradeUnit'],
                $tradeUnitData['data']
            );
        }

        if ($productVariant->is_main) {
            $product->updateQuietly(
                [
                    'product_variant_id' => $productVariant->id
                ]
            );
        }
        ProductHydrateProductVariants::dispatch($product);

        $historicProductVariant = StoreHistoricProductVariant::run($productVariant);
        $productVariant->updateQuietly(
            [
                'current_historic_product_variant_id' => $historicProductVariant->id,
            ]
        );


        return $productVariant;
    }


    public function rules(): array
    {
        return [
            'is_main'            => ['required', 'boolean'],
            'ratio'              => ['required', 'numeric', 'gt:0'],
            'code'               => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'assets',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ProductVariantStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'               => ['required', 'max:250', 'string'],
            'price'              => ['required', 'numeric', 'min:0'],
            'source_id'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'historic_source_id' => ['sometimes', 'nullable', 'string', 'max:255'],

        ];
    }


    public function inShop(Product $product, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromShop($product->shop, $request);
        $this->handle($product, $this->validatedData);

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $product->shop);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0, $strict = true): ProductVariant
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->strict         = $strict;


        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }

}
