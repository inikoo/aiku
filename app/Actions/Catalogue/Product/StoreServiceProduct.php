<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 17:10:20 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\Service\StoreService;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductTypeEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreServiceProduct extends OrgAction
{
    use IsStoreProduct;

    private ServiceStateEnum|null $state=null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData): Product
    {

        $modelData=$this->setDataFromParent($parent, $modelData);


        data_set($modelData, 'outerable_type', 'Service');


        $productData=$modelData;

        data_set(
            $productData,
            'state',
            match(Arr::get($modelData, 'state')) {
                ServiceStateEnum::IN_PROCESS  => ProductStateEnum::IN_PROCESS,
                ServiceStateEnum::ACTIVE      => ProductStateEnum::ACTIVE,
                ServiceStateEnum::DISCONTINUED=> ProductStateEnum::DISCONTINUED,
            }
        );

        /** @var Product $product */
        $product = $parent->products()->create($productData);
        $product->stats()->create();
        $product->salesIntervals()->create();


        data_set($modelData, 'price', $product->main_outerable_price);
        data_set($modelData, 'unit', $product->main_outerable_unit);


        StoreService::make()->action($product, $modelData);

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
                'state' => ['required', Rule::enum(ServiceStateEnum::class)],
            ]
        );
    }


    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('type', ProductTypeEnum::SERVICE);

        $this->prepareProductForValidation();

        if(!$this->has('state')) {
            $this->set('state', ServiceStateEnum::IN_PROCESS);
        }

    }


    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }


}
