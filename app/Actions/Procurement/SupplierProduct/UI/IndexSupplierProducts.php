<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:54:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\SupplierProduct;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSupplierProducts extends InertiaAction
{
    public function handle($parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('supplier_products.code', 'ILIKE', "%$value%")
                    ->orWhere('supplier_products.name', 'ILIKE', "%$value%");
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(SupplierProduct::class)
            ->defaultSort('supplier_products.code')
            ->select(['supplier_products.code', 'supplier_products.slug', 'supplier_products.name'])
            ->leftJoin('supplier_product_stats', 'supplier_product_stats.supplier_product_id', 'supplier_products.id')

            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->leftJoin('agents', 'agents.id', 'supplier_products.agent_id');
                    $query->where('supplier_products.agent_id', $parent->id);
                    $query->addSelect('agents.slug as agent_slug');
                } elseif (class_basename($parent) == 'Tenant') {

                    $query->leftJoin('supplier_product_tenant', 'supplier_product_tenant.supplier_product_id', 'supplier_products.id');
                    $query->where('supplier_product_tenant.tenant_id', $parent->id);
                } elseif (class_basename($parent) == 'Supplier') {

                    $query->where('supplier_products.supplier_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations=null, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withModelOperations($modelOperations)
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

    public function asController(): LengthAwarePaginator
    {
        return $this->handle(app('currentTenant'));
    }

    public function inAgent(Agent $agent): LengthAwarePaginator
    {
        $this->validateAttributes();
        return $this->handle($agent);
    }


    public function jsonResponse(LengthAwarePaginator $supplier_products): AnonymousResourceCollection
    {
        return SupplierProductResource::collection($supplier_products);
    }


    public function htmlResponse(LengthAwarePaginator $supplier_products, ActionRequest $request): Response
    {
        //        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Procurement/SupplierProducts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('supplier_products'),
                'pageHead'    => [
                    'title' => __('supplier products'),
                ],
                'data'   => SupplierProductResource::collection($supplier_products),


            ]
        )->table($this->tableStructure());
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('supplier products'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'procurement.supplier-products.index'            =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'procurement.supplier-products.index',
                        null
                    ]
                ),
            ),


            'procurement.agents.show.supplier-products.index' =>
            array_merge(
                (new ShowAgent())->getBreadcrumbs($routeParameters['supplierProduct']),
                $headCrumb(
                    [
                        'name'      => 'procurement.agents.show.supplier-products.index',
                        'parameters'=>
                            [
                                $routeParameters['supplierProduct']->slug
                            ]
                    ]
                )
            ),
            default => []
        };
    }
}
