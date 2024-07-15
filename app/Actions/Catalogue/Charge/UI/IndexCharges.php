<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Charge\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Http\Resources\Catalogue\ChargesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Shop;
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
    use HaCatalogueAuthorisation;
    private Shop|Organisation $parent;

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


    public function handle(Shop|Organisation $parent, $prefix = null): LengthAwarePaginator
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


        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('charges.shop_id', $parent->id);
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
            ])
            ->leftJoin('charge_stats', 'charges.id', 'charge_stats.charge_id')
            ->allowedSorts(['code', 'name','shop_code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|Organisation $parent, ?array $modelOperations = null, $prefix = null, $canEdit=false): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            //     $table->elementGroup(
            //         key: $key,
            //         label: $elementGroup['label'],
            //         elements: $elementGroup['elements']
            //     );
            // }

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

            if($parent instanceof Organisation) {
                $table->column(key: 'shop_code', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            };
            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
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
                    'title'     => __('charges'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('charges')
                    ],
                    // 'actions'   => [
                    //     $this->canEdit && $request->route()->getName() == 'grp.org.shops.show.catalogue.departments.index' ? [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'tooltip' => __('new department'),
                    //         'label'   => __('department'),
                    //         'route'   => [
                    //             'name'       => 'grp.org.shops.show.catalogue.departments.create',
                    //             'parameters' => $request->route()->originalParameters()
                    //         ]
                    //     ] : false,
                    // ]
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
                        'icon'  => 'fal fa-charging-station'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.assets.charges.index' =>
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
