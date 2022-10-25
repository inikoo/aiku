<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexCustomers extends InertiaAction
{
    private Shop|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('customers.name', 'LIKE', "%$value%")
                    ->orWhere('customers.email', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Customer::class)
            ->defaultSort('customers.reference')
            ->select(['reference', 'id', 'name'])
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('customers.shop_id', $this->parent->id);
                }
            })
            ->allowedSorts(['reference', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.customers.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops)
    {
        return Inertia::render(
            'Sales/Customers',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title' => __('customers'),
                ],
                'customers'   => CustomerResource::collection($shops),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = tenant();

        return $this->handle();
    }

    public function InShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('customers')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.index' => $headCrumb(),
            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->id])
            ),
            default => []
        };
    }

}
