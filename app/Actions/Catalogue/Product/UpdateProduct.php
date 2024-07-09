<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:05:18 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\FamilyHydrateProducts;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Product\ProductStateEnum;
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

    private Product $product;

    public function handle(Product $product, array $modelData): Product
    {

        $product  = $this->update($product, $modelData);
        $changed  = $product->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price','units','unit'])) {
            $historicAsset = StoreHistoricAsset::run($product);
            $product->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($product->asset);

        if (Arr::hasAny($changed, ['state'])) {

            GroupHydrateProducts::dispatch($product->group)->delay($this->hydratorsDelay);
            OrganisationHydrateProducts::dispatch($product->organisation)->delay($this->hydratorsDelay);
            ShopHydrateProducts::dispatch($product->shop)->delay($this->hydratorsDelay);
            if($product->department_id) {
                DepartmentHydrateProducts::dispatch($product->department)->delay($this->hydratorsDelay);
            }
            if($product->family_id) {
                FamilyHydrateProducts::dispatch($product->family)->delay($this->hydratorsDelay);
            }

        }

        if(count($changed)>0) {
            ProductHydrateUniversalSearch::dispatch($product)->delay($this->hydratorsDelay);
        }


        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.products.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'status'      => ['sometimes', 'required', 'boolean'],
            'state'       => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
        ];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Product
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->product        = $product;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }

    public function jsonResponse(Asset $product): ProductResource
    {
        return new ProductResource($product);
    }
}
