<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateOrgStocksBelowMinimumStock;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\Inventory\WithInventoryAuthorisation;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Http\Resources\Inventory\OrgStockMinimumStockResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStock;
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
use Spatie\QueryBuilder\AllowedSort;

class IndexOrgStocksBelowMinimumStock extends OrgAction
{
    use WithInventoryAuthorisation;

    public const string SCOPE_BELOW_MINIMUM = 'below_minimum';
    public const string SCOPE_LEVELS = 'levels';

    private string $scope = self::SCOPE_BELOW_MINIMUM;

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->scope = self::SCOPE_BELOW_MINIMUM;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($organisation, $this->scope);
    }

    public function levels(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->scope = self::SCOPE_LEVELS;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($organisation, $this->scope);
    }

    public function handle(Organisation $organisation, string $scope = self::SCOPE_BELOW_MINIMUM, string $prefix = 'minimum_stocks'): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stocks.code', $value)
                    ->orWhereStartWith('org_stock_families.code', $value)
                    ->orWhereAnyWordStartWith('org_stocks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgStock::class);

        if ($scope === self::SCOPE_LEVELS) {
            $queryBuilder->where('org_stocks.organisation_id', $organisation->id)
                ->where('org_stocks.state', OrgStockStateEnum::ACTIVE);
        } else {
            WarehouseHydrateOrgStocksBelowMinimumStock::belowMinimumStock($queryBuilder->getEloquentBuilder(), $organisation->id);
        }

        return $queryBuilder
            ->defaultSort($scope === self::SCOPE_LEVELS ? 'org_stocks.code' : '-shortfall')
            ->select([
                'org_stocks.id',
                'org_stocks.slug',
                'org_stocks.code',
                'org_stocks.name',
                'org_stocks.quantity_in_locations as stock',
                'org_stocks.minimum_stock',
                'org_stock_families.code as family_code',
                'org_stock_families.slug as family_slug',
            ])
            ->selectRaw('org_stocks.minimum_stock - org_stocks.quantity_in_locations as shortfall')
            ->leftJoin('org_stock_families', 'org_stocks.org_stock_family_id', 'org_stock_families.id')
            ->allowedSorts([
                'code',
                'name',
                'family_code',
                'stock',
                AllowedSort::callback('minimum_stock', fn ($query, bool $descending) => $query->orderByRaw('org_stocks.minimum_stock '.($descending ? 'desc' : 'asc').' nulls last')),
                AllowedSort::callback('shortfall', fn ($query, bool $descending) => $query->orderByRaw('org_stocks.minimum_stock - org_stocks.quantity_in_locations '.($descending ? 'desc' : 'asc').' nulls last')),
            ])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, ?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withLabelRecord([__('SKO'), __('SKOs')])
                ->defaultSort($this->scope === self::SCOPE_LEVELS ? 'code' : '-shortfall')
                ->column(key: 'code', label: __('Part'), sortable: true, searchable: true)
                ->column(key: 'family_code', label: __('Family'), sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), sortable: true, searchable: true)
                ->column(key: 'stock', label: __('Stock'), sortable: true, align: 'right')
                ->column(key: 'minimum_stock', label: __('Minimum'), sortable: true, align: 'right')
                ->column(key: 'shortfall', label: __('Shortfall'), sortable: true, align: 'right');
        };
    }

    public function jsonResponse(LengthAwarePaginator $orgStocks): AnonymousResourceCollection
    {
        return OrgStockMinimumStockResource::collection($orgStocks);
    }

    public function getSubNavigation(array $routeParameters): array
    {
        return [
            [
                'label'    => __('Below minimum'),
                'leftIcon' => ['icon' => 'fal fa-arrow-to-bottom'],
                'root'     => 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.index',
                'route'    => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.index',
                    'parameters' => $routeParameters,
                ],
                'number'   => $this->warehouse->stats->number_org_stocks_below_minimum_stock,
            ],
            [
                'label'    => __('Set minimum levels'),
                'leftIcon' => ['icon' => 'fal fa-sliders-h'],
                'root'     => 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.levels',
                'route'    => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.levels',
                    'parameters' => $routeParameters,
                ],
            ],
        ];
    }

    public function htmlResponse(LengthAwarePaginator $orgStocks, ActionRequest $request): Response
    {
        $routeParameters = $request->route()->originalParameters();

        $title = $this->scope === self::SCOPE_LEVELS ? __('Minimum stock levels') : __('Below minimum stock');

        return Inertia::render(
            'Org/Inventory/OrgStocksBelowMinimumStock',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $routeParameters),
                'title'       => $title,
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => [
                        'icon'  => ['fal', 'fa-arrow-to-bottom'],
                        'title' => $title,
                    ],
                    'afterTitle'    => [
                        'label' => $this->scope === self::SCOPE_LEVELS
                            ? __('Set the stock each SKO should not fall below, e.g. packaging or parts of sets')
                            : __('SKOs with stock in all locations below their minimum, to reorder'),
                    ],
                    'subNavigation' => $this->getSubNavigation($routeParameters),
                ],
                'orgStocks'   => OrgStockMinimumStockResource::collection($orgStocks),
                'canEdit'     => $this->canEdit,
            ]
        )->table($this->tableStructure(prefix: 'minimum_stocks'));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters,
                        ],
                        'label' => $routeName === 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.levels'
                            ? __('Minimum stock levels')
                            : __('Below minimum stock'),
                        'icon'  => 'fal fa-arrow-to-bottom',
                    ],
                ],
            ]
        );
    }
}
