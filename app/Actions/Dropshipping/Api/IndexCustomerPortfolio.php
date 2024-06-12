<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 15:30:56 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Api;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\CustomerPortfolioResource;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;

class IndexCustomerPortfolio extends GrpAction
{
    private Customer $customer;

    public function prepareForValidation(ActionRequest $request): void
    {
        if($request->user()->id!=$this->customer->group_id) {
            abort(404);
        }

        if($this->customer->shop->type!=ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }
    }

    public function asController(Customer $customer, ActionRequest $request): LengthAwarePaginator
    {

        $this->customer=$customer;
        $this->initialisation($request->user(), $request);

        return $this->handle($customer);
    }


    public function handle(Customer $customer): LengthAwarePaginator
    {
        $queryBuilder = QueryBuilder::for(DropshippingCustomerPortfolio::class);
        $queryBuilder->where('customer_id', $customer->id);
        $queryBuilder->leftJoin('products', 'products.id', '=', 'dropshipping_customer_portfolios.product_id');


        return $queryBuilder
            ->defaultSort('products.slug')
            ->select([
                'dropshipping_customer_portfolios.id',
                'dropshipping_customer_portfolios.reference',
                'dropshipping_customer_portfolios.customer_id',
                'dropshipping_customer_portfolios.status',
                'dropshipping_customer_portfolios.last_added_at',
                'dropshipping_customer_portfolios.last_removed_at',
                'dropshipping_customer_portfolios.created_at',
                'dropshipping_customer_portfolios.updated_at',
                'products.name as product_name',
                'products.slug as product_slug',
                'products.id as product_id',
                'products.code as product_code',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',

            ])

            ->allowedSorts(['products.name', 'products.slug', 'products.id'])
            ->withPaginator(null)
            ->withQueryString();
    }


    public function jsonResponse($products): AnonymousResourceCollection
    {
        return CustomerPortfolioResource::collection($products);
    }


}
