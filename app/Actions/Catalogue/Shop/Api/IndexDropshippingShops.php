<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Shop\Api;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ShopResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexDropshippingShops extends GrpAction
{
    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $group = $request->user();
        $this->initialisation($group, $request);

        return $this->handle($group);
    }


    public function handle(Group $group): LengthAwarePaginator
    {
        $queryBuilder = QueryBuilder::for(Shop::class);
        $queryBuilder->where('type', '=', ShopTypeEnum::DROPSHIPPING);
        $queryBuilder->where('group_id', $group->id);
        $queryBuilder->whereIn('state', [ShopStateEnum::OPEN, ShopStateEnum::CLOSING_DOWN]);


        return $queryBuilder
            ->defaultSort('shops.code')
            ->select(['code', 'id', 'name', 'slug', 'type', 'state'])
            ->allowedSorts(['code', 'name', 'type', 'state'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($shops): AnonymousResourceCollection
    {
        return ShopResource::collection($shops);
    }


}
