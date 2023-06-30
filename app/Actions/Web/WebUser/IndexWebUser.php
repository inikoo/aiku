<?php

/** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 02:34:12 Greenwich Mean Time, Plane HK-KL
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\InertiaAction;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Web\InertiaTableWebUserResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use App\Models\Web\Website;
use App\Models\Web\WebUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWebUser extends InertiaAction
{
    private Shop|Tenant|Customer $parent;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('webusers.email', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(WebUser::class)
            ->defaultSort('customers.email')
            ->select(['email', 'webusers.id'])
            //->leftJoin('shops', 'shops.id', 'shop_id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Customer') {
                    $query->where('webusers.customer_id', $this->parent->id);
                } elseif (class_basename($this->parent) == 'Website') {
                    $query->where('webusers.website_id', $this->parent->id);
                }
            })
            ->allowedSorts(['email'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('crm.customers.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops): Response
    {
        return Inertia::render(
            'Web/WebUsers',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('web users'),
                'pageHead'    => [
                    'title' => __('web users'),
                ],
                'customers'   => InertiaTableWebUserResource::collection($shops),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('email');
        });
    }


    public function inTenant(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent    = app('currentTenant');

        return $this->handle();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomerInShop(Shop $shop, Customer $customer): LengthAwarePaginator
    {
        $this->parent = $customer;


        return $this->handle();
    }

    public function inCustomerInTenant(Customer $customer): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Customer|Website|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('web users')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.show.web-users.index' => array_merge(
                (new ShowCustomer())->getBreadcrumbs('customers.show', $parent),
                $headCrumb([$parent->slug])
            ),
            'shops.show.customers.show.web-users.index' =>
            array_merge(
                (new ShowCustomer())->getBreadcrumbs('shops.show.customers.show', $parent),
                $headCrumb([$parent->shop->slug,$parent->slug])
            ),
            default => []
        };
    }
}
