<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 15:37:46 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\ApiToDeleteOrReuse;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ProductCustomersResource;
use App\Models\Catalogue\Product;
use App\Models\Dropshipping\Portfolio;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexProductCustomers extends GrpAction
{
    private Product $product;

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->user()->id != $this->product->group_id) {
            abort(404);
        }

        if ($this->product->shop->type != ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }
    }

    public function asController(Product $product, ActionRequest $request): LengthAwarePaginator
    {
        $this->product = $product;
        $this->initialisation($request->user(), $request);

        return $this->handle($product);
    }

    public function handle(Product $product): LengthAwarePaginator
    {
        $queryBuilder = QueryBuilder::for(Portfolio::class);
        $queryBuilder->where('product_id', $product->id);
        $queryBuilder->leftJoin('customers', 'customers.id', '=', 'portfolios.customer_id');


        return $queryBuilder
            ->defaultSort('customers.slug')
            ->select([
                'portfolios.id',
                'portfolios.reference',
                'portfolios.customer_id',
                'portfolios.status',
                'portfolios.last_added_at',
                'portfolios.last_removed_at',
                'portfolios.created_at',
                'portfolios.updated_at',
                'customers.name as customer_name',
                'customers.slug as customer_slug',
                'customers.id as customer_id',
                'customers.reference as customer_reference',
                'customers.contact_name as customer_contact_name',
                'customers.email as customer_contact_email',
                'customers.created_at as customer_created_at',
                'customers.updated_at as customer_updated_at',

            ])
            ->allowedSorts(['customers.name', 'customers.slug', 'customers.id'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($products): AnonymousResourceCollection
    {
        return ProductCustomersResource::collection($products);
    }


}
