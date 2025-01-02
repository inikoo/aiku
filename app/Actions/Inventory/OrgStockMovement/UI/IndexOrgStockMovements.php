<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 31-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Inventory\OrgStockMovement\UI;

use App\Actions\Inventory\HasInventoryAuthorisation;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementFlowEnum;
use App\Http\Resources\Inventory\OrgStockFamiliesResource;
use App\Http\Resources\Inventory\OrgStockMovementsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStockMovement;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrgStockMovements extends OrgAction
{
    use HasInventoryAuthorisation;
    private string $bucket;

    private Group|Organisation $parent;

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->bucket = 'all';
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($organisation);
    }

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup($this->parent, $request);

        return $this->handle($this->parent);
    }

    protected function getElementGroups(Organisation|Group $parent): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    OrgStockMovementFlowEnum::labels(),
                    OrgStockMovementFlowEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('org_stock_movements.flow', $elements);
                }
            ],
        ];
    }

    public function handle(Group|Organisation $parent, $prefix = null, $bucket = null): LengthAwarePaginator
    {
        if ($bucket) {
            $this->bucket = $bucket;
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stocks.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }
        $queryBuilder = QueryBuilder::for(OrgStockMovement::class);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        if ($parent instanceof Group) {
            $queryBuilder->where('org_stock_movements.group_id', $parent->id);
        } else {
            $queryBuilder->where('org_stock_movements.organisation_id', $parent->id);
        }

        return $queryBuilder
           ->defaultSort('org_stock_movements.flow')
           ->select([
               'org_stock_movements.flow',
               'org_stock_movements.type',
               'org_stock_movements.class',
               'org_stock_movements.quantity',
               'org_stock_movements.org_amount',
               'org_stock_movements.grp_amount',
               'organisations.name as organisation_name',
               'organisations.slug as organisation_slug',
               'warehouses.slug as warehouse_slug',
               'warehouses.name as warehouse_name',
               'locations.slug as location_slug',
               'locations.code as location_code',
               'org_stocks.slug as org_stock_slug',
               'org_stocks.name as org_stock_name',
           ])
           ->leftJoin('organisations', 'org_stock_movements.organisation_id', 'organisations.id')
           ->leftJoin('warehouses', 'warehouses.id', 'org_stock_movements.warehouse_id')
           ->leftJoin('locations', 'locations.id', 'org_stock_movements.location_id')
           ->leftJoin('org_stocks', 'org_stocks.id', 'org_stock_movements.org_stock_id')
           ->allowedSorts(['flow', 'type', 'class', 'quantity', 'org_amount', 'grp_amount','org_stock_name', 'organisation_name'])
           ->allowedFilters([$globalSearch])
           ->withPaginator($prefix)
           ->withQueryString();
    }

    public function tableStructure(Group|Organisation $parent, $prefix = null, $bucket = 'all'): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix, $bucket) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->withGlobalSearch()
                ->column(key: 'org_stock_name', label: 'stock', canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'flow', label: 'flow', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: 'type', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'class', label: 'class', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'org_amount', label: 'amount', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: 'quantity', canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return OrgStockFamiliesResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stockFamily, ActionRequest $request): Response
    {

        if ($this->parent instanceof Group) {
            $title         = __('Org Stock Movements');
            $titlePage     = __("Org Stock Movements");
        } else {
            $title         = __('Org Stock Movements');
            $titlePage = __("Org Stock Movements");
        }

        return Inertia::render(
            'Org/Inventory/OrgStockMovements',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'         => $title,
                'pageHead'    => [
                    'title'         => $title,
                    'icon'    => [
                        'title' => $titlePage,
                        'icon'  => 'fal fa-dolly',
                    ],
                ],
                'data'        => OrgStockMovementsResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Org Stock Movements'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.overview.inventory.org-stock-movements.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }
}
