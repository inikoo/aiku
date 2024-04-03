<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 12 Mar 2024 14:15:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\StoredItemReturn\UI;

use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Storage\ShowStorageDashboard;
use App\Http\Resources\Fulfilment\StoredItemReturnsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemReturn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemReturns extends RetinaAction
{
    private FulfilmentCustomer $parent;

    /*    public function authorize(ActionRequest $request): bool
        {
            return $request->user()->hasPermissionTo("fulfilment.{$this->customer->fulfilmentCustomer->id}.view");
        }*/

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $this->customer->fulfilmentCustomer;

        return $this->handle($this->customer->fulfilmentCustomer, 'stored_item_returns');
    }

    public function handle(FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('reference', $value)
                    ->orWhereStartWith('customer_reference', $value)
                    ->orWhereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(StoredItemReturn::class);
        $queryBuilder->where('fulfilment_customer_id', $parent->id);

        return $queryBuilder
            ->defaultSort('reference')
            ->allowedSorts(['reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(FulfilmentCustomer $parent, ?array $modelOperations = null, $prefix = null): \Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix . 'Page');
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'reference', label: __('reference number'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer reference', label: __('return name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'items', label: __('items'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return StoredItemReturnsResource::collection($customers);
    }

    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {
        $container = [
            'icon'    => ['fal', 'fa-stored item-alt'],
            'tooltip' => __('Customer Fulfilment'),
            'label'   => Str::possessive($this->customer->fulfilmentCustomer->slug)
        ];

        return Inertia::render(
            'Storage/RetinaStoredItemReturns',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('stored item returns'),
                'pageHead' => [
                    'title'     => __('stored item returns'),
                    // 'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => __('return')
                    ],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'label'   => __('New Return'),
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'retina.models.stored-item-returns.store',
                                'parameters' => []
                            ]
                        ]
                    ]
                ],
                'data' => StoredItemReturnsResource::collection($customers),

            ]
        )->table($this->tableStructure($this->parent, prefix: 'stored_item_returns'));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('stored item returns'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {

            'retina.storage.stored-item-returns.index' => array_merge(
                ShowStorageDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => 'retina.storage.stored-item-returns.index',
                        'parameters' => []
                    ]
                )
            ),
        };
    }
}
