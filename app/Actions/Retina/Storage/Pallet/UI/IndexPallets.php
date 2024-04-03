<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\Pallet\UI;

use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Storage\ShowStorageDashboard;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPallets extends RetinaAction
{
    private Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent;

    protected function getElementGroups(): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    PalletStatusEnum::labels(forElements: true),
                    PalletStatusEnum::count($this->customer->fulfilmentCustomer, forElements: true)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],

        ];
    }

    public function handle(FulfilmentCustomer|Location|PalletDelivery|PalletReturn $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('pallets.customer_reference', $value)
                    ->orWhereWith('pallets.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Pallet::class);

        switch (class_basename($parent)) {
            case "FulfilmentCustomer":
                $query->where('fulfilment_customer_id', $parent->id);
                break;
            case "PalletDelivery":
                $query->where('pallet_delivery_id', $parent->id);
                break;
            case "PalletReturn":
                $query->where('pallet_return_id', $parent->id);
                break;
            default:
                $query->where('group_id', app('group')->id);
                break;
        }

        foreach ($this->getElementGroups() as $key => $elementGroup) {
            $query->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        return $query->defaultSort('reference')
            ->allowedSorts(['customer_reference', 'reference'])
            ->allowedFilters([$globalSearch, 'customer_reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups() as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => '',
                'count' => 0
            ];

            if ($parent instanceof FulfilmentCustomer) {
                $emptyStateData['description'] = __("You don't have any stored pallets");
            }

            $table->withGlobalSearch()
                ->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
            $table->column(key: 'reference', label: __('reference number'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'customer_reference', label: __('pallet name'), canBeHidden: false, sortable: false, searchable: true);
            $table->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true)
                ->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true)
                ->defaultSort('reference');
        };
    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletsResource::collection($pallets);
    }

    public function htmlResponse(LengthAwarePaginator $pallets, ActionRequest $request): Response
    {
        return Inertia::render(
            'Storage/RetinaPallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                ),
                'title'       => __('pallets'),
                'pageHead'    => [
                    'title'   => __('pallets'),
                    'icon'    => ['fal', 'fa-pallet'],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'label'   => __('New Delivery'),
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'retina.models.pallet-delivery.store',
                                'parameters' => []
                            ]
                        ]
                    ]
                ],
                'data'        => PalletsResource::collection($pallets),
            ]
        )->table($this->tableStructure($this->parent, 'pallets'));
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $this->customer->fulfilmentCustomer;

        return $this->handle($this->customer->fulfilmentCustomer, 'pallets');
    }

    public function action(PalletDelivery|PalletReturn $parent): LengthAwarePaginator
    {
        $this->customer = request()->user()->customer;
        $this->parent   = $parent;

        return $this->handle($parent, 'pallets');
    }

    public function getBreadcrumbs(string $routeName): array
    {
        return match ($routeName) {
            'retina.storage.pallets.index' =>
            array_merge(
                ShowStorageDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'retina.storage.pallets.index',
                            ],
                            'label' => __('pallets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),
        };
    }
}
