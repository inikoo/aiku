<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:21:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Charge\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Http\Resources\Catalogue\ChargesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Billables\Charge;
use App\Models\Catalogue\Shop;
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

class IndexCharges extends OrgAction
{
    use HasCatalogueAuthorisation;

    private Group|Shop|Organisation $parent;

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->parent);
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop);
    }


    public function handle(Group|Shop|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('charges.name', $value)
                    ->orWhereStartWith('charges.slug', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Charge::class);
        // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
        //     $queryBuilder->whereElementGroup(
        //         key: $key,
        //         allowedElements: array_keys($elementGroup['elements']),
        //         engine: $elementGroup['engine'],
        //         prefix: $prefix
        //     );
        // }

        $queryBuilder->leftJoin('organisations', 'charges.organisation_id', '=', 'organisations.id')
        ->leftJoin('shops', 'charges.shop_id', '=', 'shops.id');

        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('charges.shop_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('charges.group_id', $parent->id);
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('charges.organisation_id', $parent->id);
            $queryBuilder->leftJoin('shops', 'charges.shop_id', 'shops.id');
            $queryBuilder->addSelect(
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
            );
        }


        return $queryBuilder
            ->defaultSort('charges.code')
            ->select([
                'charges.slug',
                'charges.code',
                'charges.name',
                'charges.state',
                'charges.description',
                'charges.created_at',
                'charges.updated_at',
                'invoices_all',
                'sales_all',
                'customers_invoiced_all',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
            ])
            ->leftJoin('charge_stats', 'charges.id', 'charge_stats.charge_id')
            ->leftJoin('asset_sales_intervals', 'charges.asset_id', 'asset_sales_intervals.asset_id')
            ->leftJoin('asset_ordering_intervals', 'charges.asset_id', 'asset_ordering_intervals.asset_id')
            ->allowedSorts(['code', 'name', 'shop_code', 'sales_all', 'customers_invoiced_all', 'invoices_all'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Shop|Organisation $parent, ?array $modelOperations = null, $prefix = null, $canEdit = false): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->defaultSort('code')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No charges found"),
                            'description' => $canEdit && $parent->catalogueStats->number_assets_type_charge == 0 ? __('You dont have any charges yet ✨') : '',
                            'count'       => $parent->catalogueStats->number_assets_type_charge,

                        ],
                        'Shop' => [
                            'title'       => __("No charges found"),
                            'description' => $canEdit ? __('You dont have any charges yet ✨')
                                : null,
                            'count'       => $parent->stats->number_assets_type_charge,
                        ],
                        default => null
                    }
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');

            if ($parent instanceof Organisation) {
                $table->column(key: 'shop_code', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            };
            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customers_invoiced_all', label: __('customers'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'invoices_all', label: __('invoices'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'sales_all', label: __('amount'), canBeHidden: false, sortable: true, searchable: true);

            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                        ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }
        };
    }

    public function jsonResponse(LengthAwarePaginator $charges): AnonymousResourceCollection
    {
        return ChargesResource::collection($charges);
    }

    public function htmlResponse(LengthAwarePaginator $charges, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Charges',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Charges'),
                'pageHead'    => [
                    'title'   => __('charges'),
                    'icon'    => [
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('charges')
                    ],
                    'actions' => [
                        $this->canEdit && $request->route()->getName() == 'grp.org.shops.show.billables.charges.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new charge'),
                            'label'   => __('charge'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.billables.charges.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],
                'data'        => ChargesResource::collection($charges),
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
                        'label' => __('Charges'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.billables.charges.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.overview.billables.charges.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs($routeParameters),
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

    // protected function getElementGroups($parent): array
    // {
    //     return
    //         [
    //             'state' => [
    //                 'label'    => __('State'),
    //                 'elements' => array_merge_recursive(
    //                     ChargeStateEnum::labels(),
    //                     ChargeStateEnum::count($parent)
    //                 ),
    //                 'engine'   => function ($query, $elements) {
    //                     $query->whereIn('product_categories.state', $elements);
    //                 }
    //             ]
    //         ];
    // }
}
