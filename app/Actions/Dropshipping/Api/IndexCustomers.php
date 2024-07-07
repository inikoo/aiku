<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 13:35:32 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Api;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\CustomersResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexCustomers extends OrgAction
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
        $group = $request->user();
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }


    public function handle(Shop $shop): LengthAwarePaginator
    {
        $queryBuilder = QueryBuilder::for(Customer::class);
        $queryBuilder->where('shop_id', $shop->id);


        return $queryBuilder
            ->defaultSort('customers.id')
            ->allowedSorts(['name', 'id'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($customers): AnonymousResourceCollection
    {
        return CustomersResource::collection($customers);
    }


}
