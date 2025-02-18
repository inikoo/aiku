<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:05:18 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Search\ProductRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Enums\Catalogue\Product\ProductTradeConfigEnum;
use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Product;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProduct extends OrgAction
{
    use WithActionUpdate;
    use WithProductHydrators;
    use WithNoStrictRules;

    private Product $product;

    public function handle(Product $product, array $modelData): Product
    {
        if (Arr::has($modelData, 'org_stocks')) {
            $orgStocks = Arr::pull($modelData, 'org_stocks', []);
            $product->orgStocks()->sync($orgStocks);
        }


        $product = $this->update($product, $modelData);
        $changed = $product->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price', 'units', 'unit'])) {
            $historicAsset = StoreHistoricAsset::run($product, [], $this->hydratorsDelay);
            $product->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($product->asset, [], $this->hydratorsDelay);

        if (Arr::hasAny($changed, ['state'])) {
            $this->productHydrators($product);
        }

        if (count($changed) > 0) {
            ProductRecordSearch::dispatch($product);
        }


        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true; //TODO: Auth
    }

    public function rules(): array
    {
        $rules = [
            'code'         => [
                'sometimes',
                'required',
                'max:32',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
            'price'        => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'  => ['sometimes', 'required', 'max:1500'],
            'rrp'          => ['sometimes', 'required', 'numeric'],
            'data'         => ['sometimes', 'array'],
            'settings'     => ['sometimes', 'array'],
            'status'       => ['sometimes', 'required', Rule::enum(ProductStatusEnum::class)],
            'state'        => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
            'trade_config' => ['sometimes', 'required', Rule::enum(ProductTradeConfigEnum::class)],


            'org_stocks' => ['sometimes', 'present', 'array']
        ];


        if (!$this->strict) {
            $rules['org_stocks'] = ['sometimes', 'nullable', 'array'];
            $rules               = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Product
    {
        if (!$audit) {
            Product::disableAuditing();
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->product        = $product;
        $this->strict         = $strict;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }

    public function jsonResponse(Asset $product): ProductResource
    {
        return new ProductResource($product);
    }
}
