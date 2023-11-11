<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\InertiaAction;
use App\Actions\UI\CRM\CRMDashboard;
use App\Http\Resources\Lead\ProspectResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Prospect;
use App\Models\Market\Shop;
use App\Models\Organisation\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexProspects extends InertiaAction
{
    private Shop|Organisation $parent;
    private bool $canCreateShop = false;


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit       = $request->user()->can('crm.customers.edit');
        $this->canCreateShop = $request->user()->can('shops.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('crm.customers.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');

        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $shop;

        return $this->handle($shop);
    }

    public function handle(Shop|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('prospects.name', '~*', "\y$value\y")
                    ->orWhere('prospects.email', '=', $value)
                    ->orWhere('prospects.phone', '=', $value)
                    ->orWhere('prospects.website', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return QueryBuilder::for(Prospect::class)
            ->defaultSort('prospects.name')
            ->select([
                'prospects.name',
                'prospects.slug',
                'prospects.email',
                'prospects.phone',
                'prospects.website',
                'prospects.id as shop_id',
                'shops.code as shop_code',
                'shops.slug as shop_slug',
            ])
            ->leftJoin('shops', 'shops.id', 'shop_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('prospects.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['name', 'email', 'phone', 'website'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No prospects found"),
                            'description' => $this->canCreateShop && $parent->marketStats->number_shops == 0 ? __('Get started by creating a shop. ✨')
                                : __("In fact, is no even a shop yet 🤷🏽‍♂️"),
                            'count'       => $parent->crmStats->number_prospects,
                            'action'      => $this->canCreateShop && $parent->marketStats->number_shops == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name' => 'shops.create',
                                ]
                            ] : null
                        ],
                    }
                    /*
                    [
                        'title'       => __('no customers'),
                        'description' => $this->canEdit ? __('Get started by creating a new customer.') : null,
                        'count'       => app('currentTenant')->stats->number_employees,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new customer'),
                            'label'   => __('customer'),
                            'route'   => [
                                'name'       => 'crm.customers.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]
                    */
                )
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'phone', label: __('phone'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'website', label: __('website'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $prospects): AnonymousResourceCollection
    {
        return ProspectResource::collection($prospects);
    }

    public function htmlResponse(LengthAwarePaginator $prospects, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'CRM/Prospects',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters(),
                ),
                'title'       => __('prospects'),
                'pageHead'    => [
                    'title'     => __('prospects'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-user-plus'],
                        'title' => __('prospect')
                    ]
                ],
                'data'        => ProspectResource::collection($prospects),


            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('prospects'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'crm.prospects.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'crm.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => 'crm.prospects.index',
                    ]
                ),
            ),
            'crm.shops.show.prospects.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'crm.shops.show.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'crm.shops.show.prospects.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
