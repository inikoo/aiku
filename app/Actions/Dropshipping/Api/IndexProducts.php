<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 14:14:33 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Api;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ProductsResource;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexProducts extends OrgAction
{
    public function prepareForValidation(ActionRequest $request): void
    {
        if($request->user()->id!=$this->shop->group_id) {
            abort(404);
        }
        if($this->shop->type!=ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }
    }

    public function asController(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop);
    }


    public function handle(Shop $shop): LengthAwarePaginator
    {
        $queryBuilder = QueryBuilder::for(Product::class);
        $queryBuilder->where('shop_id', $shop->id);


        return $queryBuilder
            ->defaultSort('products.id')
            ->allowedSorts(['code', 'slug', 'id'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($products): AnonymousResourceCollection
    {
        return ProductsResource::collection($products);
    }


}
