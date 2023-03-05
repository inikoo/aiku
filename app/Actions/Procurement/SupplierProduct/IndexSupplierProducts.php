<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\InertiaAction;
use App\Actions\Procurement\ShowProcurementDashboard;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSupplierProducts extends InertiaAction
{
    private Agent|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('supplier_products.code', 'LIKE', "$value%")
                    ->orWhere('supplier_products.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(SupplierProduct::class)
            ->defaultSort('supplier_products.code')
            ->select(['code', 'slug', 'name'])
            ->where('supplier_products.slug', 'supplier_products', 'supplier_products.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Agent') {
                    $query->where('supplier_products.current_historic_supplier_product_id', $this->parent->id);
                }
            })
            ->leftJoin('supplier_product_stats', 'supplier_product_stats.supplier_product_id', 'supplier_products.id')
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
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $request->validate();
        $this->parent    = app('currentTenant');
        return $this->handle();
    }

    public function InAgent(Agent $agent): LengthAwarePaginator
    {
        $this->parent = $agent;
        $this->validateAttributes();
        return $this->handle();
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return SupplierProductResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $supplier_products)
    {
        return Inertia::render(
            'Procurement/SupplierProducts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('supplier_products'),
                'pageHead'    => [
                    'title' => __('supplier_products'),
                ],
                'supplier_products'   => SupplierProductResource::collection($supplier_products),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs(),
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
