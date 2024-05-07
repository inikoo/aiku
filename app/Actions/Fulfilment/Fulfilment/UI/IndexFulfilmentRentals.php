<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:15:33 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\UI\Fulfilment\RentalsTabsEnum;
use App\Http\Resources\Market\RentalsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFulfilmentRentals extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    RentalStateEnum::labels(),
                    RentalStateEnum::count($parent->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('rentals.name', $value)
                    ->orWhereStartWith('rentals.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Rental::class);
        $queryBuilder->where('rentals.shop_id', $parent->shop_id);
        $queryBuilder->join('products', 'rentals.product_id', '=', 'products.id');
        $queryBuilder->join('currencies', 'products.currency_id', '=', 'currencies.id');


        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('rentals.id')
            ->select([
                'rentals.id',
                'rentals.state',
                'rentals.auto_assign_asset',
                'rentals.auto_assign_asset_type',
                'rentals.created_at',
                'rentals.price',
                'rentals.unit',
                'products.name',
                'products.code',
                'products.main_outerable_price',
                'products.description',
                'currencies.code as currency_code',
            ]);


        return $queryBuilder->allowedSorts(['id'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RentalsTabsEnum::values());

        return $this->handle($fulfilment, RentalsTabsEnum::RENTALS->value);
    }

    public function htmlResponse(LengthAwarePaginator $rentals, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Rentals',
            [
                'title'       => __('fulfilment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'   => __('rentals'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'primary',
                            'icon'  => 'fal fa-plus',
                            'label' => __('create rental'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.products.rentals.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => RentalsTabsEnum::navigation()
                ],

                RentalsTabsEnum::RENTALS->value => $this->tab == RentalsTabsEnum::RENTALS->value ?
                    fn () => RentalsResource::collection($rentals)
                    : Inertia::lazy(fn () => RentalsResource::collection($rentals)),

            ]
        )->table(
            $this->tableStructure(
                parent: $this->fulfilment,
                prefix: RentalsTabsEnum::RENTALS->value
            )
        );
    }

    public function tableStructure(
        Fulfilment $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
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
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title' => __("No rentals found"),
                            'count' => $parent->shop->stats->number_products_type_rental,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'state', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'workflow', label: __('workflow'), canBeHidden: false, sortable: true, searchable: true, className: 'hello')
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $rentals): AnonymousResourceCollection
    {
        return RentalsResource::collection($rentals);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (array $routeParameters = []) use ($suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('rentals'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return
            array_merge(
                IndexFulfilmentProducts::make()->getBreadcrumbs(routeParameters: $routeParameters, icon: 'fal fa-cube'),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.products.rentals.index',
                        'parameters' => $routeParameters
                    ]
                )
            );
    }


}
