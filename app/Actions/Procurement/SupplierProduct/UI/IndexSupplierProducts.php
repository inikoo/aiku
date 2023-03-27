<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:54:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\SupplierProduct;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSupplierProducts extends InertiaAction
{
    public function handle(Agent|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('supplier_products.code', 'LIKE', "$value%")
                    ->orWhere('supplier_products.name', 'LIKE', "%$value%");
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SUPPLIER_PRODUCTS->value);

        return QueryBuilder::for(SupplierProduct::class)
            ->defaultSort('supplier_products.code')
            ->select(['code', 'slug', 'name'])
            ->where('supplier_products.slug', 'supplier_products', 'supplier_products.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('supplier_products.current_historic_supplier_product_id', $parent->id);
                }
            })
            ->leftJoin('supplier_product_stats', 'supplier_product_stats.supplier_product_id', 'supplier_products.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::SUPPLIER_PRODUCTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::SUPPLIER_PRODUCTS->value)
                ->pageName(TabsAbbreviationEnum::SUPPLIER_PRODUCTS->value.'Page');

            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $request->validate();
        return $this->handle(app('currentTenant'));
    }

    public function InAgent(Agent $agent): LengthAwarePaginator
    {
        $this->validateAttributes();
        return $this->handle($agent);
    }

    public function jsonResponse(LengthAwarePaginator $supplier_products): AnonymousResourceCollection
    {
        return SupplierProductResource::collection($supplier_products);
    }


    public function htmlResponse(LengthAwarePaginator $supplier_products, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Procurement/SupplierProducts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('supplier_products'),
                'pageHead'    => [
                    'title' => __('supplier products'),
                ],
                'data'   => SupplierProductResource::collection($supplier_products),


            ]
        )->table($this->tableStructure($parent));
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                'procurement.supplier-products.index' => [
                    'route'      => 'procurement.supplier-products.index',
                    'modelLabel' => [
                        'label' => __('supplier_products')
                    ],
                ],
            ]
        );
    }
}
