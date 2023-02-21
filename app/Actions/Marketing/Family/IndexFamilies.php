<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 Febr 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\InertiaTableCustomerResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
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


class IndexFamilies extends InertiaAction
{
    private Shop|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {


                $query->where('families.name', '~*', "\y$value\y")
                    ->orWhere('families.code', '=', $value);
            });
        });


        return QueryBuilder::for(Family::class)
            ->defaultSort('families.code')
            ->select(['families.code', 'families.name', 'families.state', 'families.created_at', 'families.updated_at', 'families.slug', 'shops.slug as shop_slug'])
            ->leftJoin('family_stats', 'families.id', 'family_stats.family_id')
            ->leftJoin('shops', 'families.shop_id', 'shops.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('families.shop_id', $this->parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return FamilyResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $families)
    {
        return Inertia::render(
            'Marketing/Family',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title' => __('families'),
                'pageHead' => [
                    'title' => __('families'),
                ],
                'families' => FamilyResource::collection($families),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = tenant();
        $this->routeName = $request->route()->getName();

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
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }

}
