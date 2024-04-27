<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Apr 2024 11:59:05 Malaysia Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreRentalProduct extends OrgAction
{
    use IsStoreProduct;

    private RentalStateEnum|null $state = null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData): Product
    {
        $modelData = $this->setDataFromParent($parent, $modelData);

        data_set($modelData, 'unit_relationship_type', ProductUnitRelationshipType::TIME_INTERVAL->value);
        data_set($modelData, 'outerable_type', 'Rental');


        $productData = $modelData;

        data_set(
            $productData,
            'state',
            match (Arr::get($modelData, 'state')) {
                RentalStateEnum::IN_PROCESS   => ProductStateEnum::IN_PROCESS,
                RentalStateEnum::ACTIVE       => ProductStateEnum::ACTIVE,
                RentalStateEnum::DISCONTINUED => ProductStateEnum::DISCONTINUED,
            }
        );

        /** @var Product $product */
        $product = $parent->products()->create(Arr::except($productData, ['auto_assign_asset', 'auto_assign_asset_type']));
        $product->stats()->create();
        $product->salesStats()->create();



        data_set($modelData, 'price', $product->main_outerable_price);
        data_set($modelData, 'unit', $product->main_outerable_unit);


        StoreRental::make()->action($product, $modelData);


        ShopHydrateProducts::dispatch($product->shop);
        OrganisationHydrateProducts::dispatch($product->organisation);
        GroupHydrateProducts::dispatch($product->group);
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function rules(): array
    {
        return array_merge(
            $this->getProductRules(),
            [
                'state'                  => ['required', Rule::enum(RentalStateEnum::class)],
                'auto_assign_asset'      => ['nullable', 'string', 'in:Pallet,StoredItem'],
                'auto_assign_asset_type' => ['nullable', 'string', 'in:pallet,box,oversize'],

            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('type', ProductTypeEnum::RENTAL);

        $this->prepareProductForValidation();
        if (!$this->has('state')) {
            $this->set('state', RentalStateEnum::IN_PROCESS);
        }
    }


    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }


}
