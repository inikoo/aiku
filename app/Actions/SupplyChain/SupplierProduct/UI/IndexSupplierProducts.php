<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:54:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct\UI;

use App\Actions\GrpAction;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Group;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexSupplierProducts extends GrpAction
{
    public function handle(Group|Agent|Supplier $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('supplier_products.code', $value)
                    ->orWhereANyWordStartWith('supplier_products.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(SupplierProduct::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('supplier_products.code')
            ->select([
                'supplier_products.code',
                'supplier_products.slug',
                'supplier_products.name'
            ])
            ->leftJoin('supplier_product_stats', 'supplier_product_stats.supplier_product_id', 'supplier_products.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->leftJoin('agents', 'agents.id', 'supplier_products.agent_id');
                    $query->where('supplier_products.agent_id', $parent->id);
                    $query->addSelect('agents.slug as agent_slug');
                } elseif (class_basename($parent) == 'Supplier') {
                    $query->where('supplier_products.supplier_id', $parent->id);
                } else {
                    $query->where('supplier_products.group_id', $this->group->if);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations = null, $prefix = null): Closure
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
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
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

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);
        return $this->handle($this->group);
    }

    public function inAgent(Agent $agent, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($agent);
    }

    public function inSupplier(Supplier $supplier, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($supplier);
    }

    public function asController(Agent $agent, Supplier $supplier, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($supplier);
    }


    public function jsonResponse(LengthAwarePaginator $supplier_products): AnonymousResourceCollection
    {
        return SupplierProductResource::collection($supplier_products);
    }


    public function htmlResponse(LengthAwarePaginator $supplier_products, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/SupplierProducts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('supplier_products'),
                'pageHead'    => [
                    'title' => __('supplier products'),
                ],
                'data'        => SupplierProductResource::collection($supplier_products),


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
            'grp.procurement.supplier-products.index' =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.procurement.supplier-products.index',
                        null
                    ]
                ),
            ),


            'grp.procurement.agents.show.supplier-products.index' =>
            array_merge(
                (new ShowOrgAgent())->getBreadcrumbs($routeParameters['supplierProduct']),
                $headCrumb(
                    [
                        'name'       => 'grp.procurement.agents.show.supplier-products.index',
                        'parameters' =>
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
