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
use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
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


        $price=Arr::get($modelData, 'price');
        data_forget($modelData, 'price');
        data_set($modelData, 'price', $price);


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
