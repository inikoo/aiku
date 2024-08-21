<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Aug 2024 14:56:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily\UI;

use App\Actions\Inventory\HasInventoryAuthorisation;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Actions\OrgAction;
use App\Enums\Inventory\OrgStockFamily\OrgStockFamilyStateEnum;
use App\Http\Resources\Inventory\OrgStockFamiliesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrgStockFamilies extends OrgAction
{
    use HasInventoryAuthorisation;

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($organisation);
    }

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->initialisation($organisation, $request);
        $this->parent = $organisation;

        return $this->handle($organisation);
    }


    protected function getElementGroups(Organisation $organisation): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        OrgStockFamilyStateEnum::labels(),
                        OrgStockFamilyStateEnum::count($organisation)
                    ),
                    'engine'   => function ($query, $elements) {
                        $query->whereIn('org_stock_families.state', $elements);
                    }
                ]
            ];
    }

    public function handle(Organisation $organisation, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stock_families.code', $value)
                    ->orWhereAnyWordStartWith('org_stock_families.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgStockFamily::class);
        $queryBuilder->where('org_stock_families.organisation_id', $organisation->id);
        foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        return $queryBuilder
           ->defaultSort('code')
           ->select([
               'slug',
               'code',
               'org_stock_families.id as id',
               'name',
               'number_current_org_stocks'
           ])
           ->leftJoin('org_stock_family_stats', 'org_stock_family_stats.org_stock_family_id', 'org_stock_families.id')
           ->allowedSorts(['code', 'name', 'number_current_org_stocks'])
           ->allowedFilters([$globalSearch])
           ->withPaginator($prefix)
           ->withQueryString();
    }

    public function tableStructure(Organisation $organisation, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($organisation, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: 'code', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_current_org_stocks', label: 'SKUs', canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return OrgStockFamiliesResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stockFamily, ActionRequest $request): Response
    {
        $organisation = $this->organisation;

        return Inertia::render(
            'Org/Inventory/OrgStockFamilies',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __("SKUs families"),
                'pageHead'    => [
                    'title'   => __("SKUs families"),
                    'icon'    => [
                        'title' => __("SKUs families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                ],
                'data'        => OrgStockFamiliesResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($organisation));
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __("SKUs families"),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
