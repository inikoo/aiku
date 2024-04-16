<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 17:10:20 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Market\Service\StoreService;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreNoPhysicalGood extends OrgAction
{
    use IsStoreProduct;

    private ProductStateEnum|null $state=null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData): Product
    {

        $modelData=$this->setDataFromParent($parent, $modelData);



        if(Arr::get($modelData, 'type')==ProductTypeEnum::RENTAL) {
            data_set($modelData, 'unit_relationship_type', ProductUnitRelationshipType::TIME_INTERVAL->value);
            data_set($modelData, 'outerable_type', 'Rental');
        } elseif(Arr::get($modelData, 'type')==ProductTypeEnum::SERVICE) {
            data_set($modelData, 'outerable_type', 'Service');
        }

        /** @var Product $product */
        $product = $parent->products()->create($modelData);
        $product->stats()->create();

        if(Arr::get($modelData, 'type')==ProductTypeEnum::RENTAL) {

        } elseif(Arr::get($modelData, 'type')==ProductTypeEnum::SERVICE) {

            data_set(
                $modelData,
                'state',
                match(Arr::get($modelData, 'state')) {
                    ProductStateEnum::IN_PROCESS => ServiceStateEnum::IN_PROCESS,
                    ProductStateEnum::ACTIVE     => ServiceStateEnum::ACTIVE,
                    ProductStateEnum::DISCONTINUING , ProductStateEnum::DISCONTINUED=> ServiceStateEnum::DISCONTINUED,
                }
            );


            $service=StoreService::make()->action($product, $modelData);

        }


        ShopHydrateProducts::dispatch($product->shop);
        OrganisationHydrateProducts::dispatch($product->organisation);
        GroupHydrateProducts::dispatch($product->group);
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function rules(): array
    {
        return $this->getProductRules();
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->prepareProductForValidation();
    }


    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }


}
