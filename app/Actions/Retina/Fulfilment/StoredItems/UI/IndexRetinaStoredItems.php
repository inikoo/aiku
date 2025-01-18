<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\StoredItems\UI;

use App\Actions\Retina\Fulfilment\UI\ShowRetinaStorageDashboard;
use App\Actions\RetinaAction;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexRetinaStoredItems extends RetinaAction
{
    public function handle(FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(StoredItem::class)
            ->defaultSort('slug')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == "FulfilmentCustomer") {
                    $query->where('fulfilment_customer_id', $parent->id);
                }
            })
            ->allowedSorts(['slug', 'state'])
            ->allowedFilters([$globalSearch, 'slug', 'state'])
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
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'         => __("No stored items found"),
                        'count'         => $parent->count(),
                        'description'   => __("No items stored in any pallets")
                    ]
                )
                ->column(key: 'state', label: __('State'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('Location'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return StoredItemResource::collection($storedItems);
    }


    public function htmlResponse(LengthAwarePaginator $storedItems, ActionRequest $request): Response
    {
        return Inertia::render(
            'Storage/RetinaStoredItems',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __("SKUs"),
                'pageHead'    => [
                    'title'   => __("SKUs"),

                ],
                'data' => StoredItemResource::collection($storedItems),
            ]
        )->table($this->tableStructure($storedItems));
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;

        $this->initialisation($request);

        return $this->handle($fulfilmentCustomer);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowRetinaStorageDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'retina.fulfilment.storage.stored-items.index'
                        ],
                        'label' => __("SKUs"),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
