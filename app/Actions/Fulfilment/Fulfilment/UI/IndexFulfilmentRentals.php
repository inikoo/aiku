<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:15:33 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Http\Resources\Market\RentalsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Rental;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
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


        /*        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $queryBuilder->whereElementGroup(
                        key: $key,
                        allowedElements: array_keys($elementGroup['elements']),
                        engine: $elementGroup['engine'],
                        prefix: $prefix
                    );
                }*/



        $queryBuilder
            ->defaultSort('rentals.id')
            ->select([
                'rentals.id',
                'rentals.state',
                'rentals.auto_assign_asset',
                'rentals.auto_assign_asset_type',
                'rentals.created_at',
                'rentals.updated_at',
                'products.name',
                'products.code',
                'products.main_outerable_price',
                'products.description'
            ]);


        return $queryBuilder->allowedSorts(['id'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, 'rentals');
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
                    'title' => __('rentals'),
                ],

                'data' => RentalsResource::collection($rentals)
            ]
        )->table($this->tableStructure(
            parent:$this->fulfilment,
            prefix: 'rentals'
        ));
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
                    ->pageName($prefix . 'Page');
            }

            /*            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                            $table->elementGroup(
                                key: $key,
                                label: $elementGroup['label'],
                                elements: $elementGroup['elements']
                            );
                        }*/

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title' => __("No rentals found"),
                            'count' => $parent->shop->stats->number_rentals,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'type', label: '', canBeHidden: false)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $rentals): AnonymousResourceCollection
    {
        return RentalsResource::collection($rentals);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {

        $fulfilment = Fulfilment::where('slug', Arr::get($routeParameters, 'fulfilment'))->first();

        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.index',
                                    'parameters' => Arr::only($routeParameters, 'organisation')
                                ],
                                'label' => __('fulfilment'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                                    'parameters' => $routeParameters
                                ],
                                'label' => $fulfilment?->shop?->name,
                                'icon'  => 'fal fa-bars'
                            ]

                        ],
                        'suffix'         => $suffix,
                    ]
                ]
            );
    }

    public function getPrevious(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $previous = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '<', $fulfilment->shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->fulfilment, $request->route()->getName());
    }

    public function getNext(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $next = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '>', $fulfilment->shop->code)->orderBy('code')->first();

        return $this->getNavigation($next?->fulfilment, $request->route()->getName());
    }

    private function getNavigation(?Fulfilment $fulfilment, string $routeName): ?array
    {
        if (!$fulfilment) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.operations.dashboard' => [
                'label' => $fulfilment->shop?->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'fulfilment'   => $fulfilment->slug
                    ]

                ]
            ]
        };
    }
}
