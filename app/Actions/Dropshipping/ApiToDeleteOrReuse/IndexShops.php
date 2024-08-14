<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 13:35:32 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\ApiToDeleteOrReuse;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ShopsResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexShops extends GrpAction
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
            ->select(['id'])
            ->allowedSorts(['code', 'name', 'type', 'state'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($shops): AnonymousResourceCollection
    {
        return ShopsResource::collection($shops);
    }


}
