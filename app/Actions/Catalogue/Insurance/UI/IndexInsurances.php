<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Http\Resources\Catalogue\ChargesResource;
use App\Http\Resources\Catalogue\InsurancesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Insurance;
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

class IndexInsurances extends OrgAction
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
                $query->whereAnyWordStartWith('insurances.name', $value)
                    ->orWhereStartWith('insurances.slug', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Insurance::class);
        // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
        //     $queryBuilder->whereElementGroup(
        //         key: $key,
        //         allowedElements: array_keys($elementGroup['elements']),
        //         engine: $elementGroup['engine'],
        //         prefix: $prefix
        //     );
        // }


        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('insurances.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('insurances.organisation_id', $parent->id);
            $queryBuilder->leftJoin('shops', 'insurances.shop_id', 'shops.id');
            $queryBuilder->addSelect(
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
            );
        }



        return $queryBuilder
            ->defaultSort('insurances.code')
            ->select([
                'insurances.slug',
                'insurances.code',
                'insurances.name',
                'insurances.state',
                'insurances.description',
                'insurances.created_at',
                'insurances.updated_at',
            ])
            ->leftJoin('insurance_stats', 'insurances.id', 'insurance_stats.insurance_id')
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
                            'title'       => __("No insurances found"),
                            'description' => $canEdit && $parent->catalogueStats->number_assets_type_insurance == 0 ? __('You dont have any insurances yet ✨') : '',
                            'count'       => $parent->catalogueStats->number_assets_type_insurance,

                        ],
                        'Shop' => [
                            'title'       => __("No insurances found"),
                            'description' => $canEdit ? __('You dont have any insurances yet ✨')
                                : null,
                            'count'       => $parent->stats->number_assets_type_insurance,
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

    public function jsonResponse(LengthAwarePaginator $insurances): AnonymousResourceCollection
    {
        return InsurancesResource::collection($insurances);
    }

    public function htmlResponse(LengthAwarePaginator $insurances, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/Catalogue/Insurances',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Insurances'),
                'pageHead'    => [
                    'title'     => __('insurances'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-house-damage'],
                        'title' => __('insurances')
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
                'data'        => InsurancesResource::collection($insurances),
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
                        'label' => __('Insurances'),
                        'icon'  => 'fal fa-house-damage'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.assets.insurances.index' =>
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
