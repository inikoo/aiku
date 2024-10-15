<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-10h-15m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\UI\Goods\Catalogue;

use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Http\Resources\Goods\Catalogue\MasterShopsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\MasterShop;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexMasterShops extends GrpAction
{
    use WithMasterCatalogueSubnavigation;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("goods.{$this->group->id}.view");
    }

    public function handle(Group $group, $prefix = null, $bucket = null): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('master_shops.code', $value)
                    ->orWhereStartWith('master_shops.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(MasterShop::class);

        $queryBuilder->where('master_shops.group_id', $group->id);

        return $queryBuilder
            ->defaultSort('master_shops.code')
            ->select(
                [
                        'master_shops.id',
                        'master_shops.code',
                        'master_shops.name',
                        'master_shops.slug',
                        'master_shops.state',
                        'master_shops.type',
                        'master_shops.image_id',
                    ]
            )
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null)
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
                ->withEmptyState(
                    [
                            'title'       => __("No master shops found"),
                        ],
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $masterShops): AnonymousResourceCollection
    {
        return MasterShopsResource::collection($masterShops);
    }

    public function htmlResponse(LengthAwarePaginator $masterShops, ActionRequest $request): Response
    {
        $subNavigation = $this->getMasterCatalogueSubnavigation($this->group);

        return Inertia::render(
            'Goods/MasterShops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('master shops'),
                'pageHead'    => [
                    'title'         => __('Master Shops'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-store-alt'],
                        'title' => __('master shops')
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'data'        => MasterShopsResource::collection($masterShops),

            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Master shops'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.goods.catalogue.shops.index' =>
            array_merge(
                ShowGoodsDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => []
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $group = group();
        $this->initialisation($group, $request);
        return $this->handle($group, $request);
    }

}