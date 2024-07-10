<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 20:05:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\RecurringBill\UI;

use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Billing\UI\ShowBillingDashboard;
use App\Enums\UI\Fulfilment\RecurringBillsTabsEnum;
use App\Http\Resources\Fulfilment\RecurringBillsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RecurringBill;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexRecurringBills extends RetinaAction
{
    private FulfilmentCustomer $parent;

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        $this->parent = $this->customer->fulfilmentCustomer;

        return $this->handle($this->customer->fulfilmentCustomer);
    }

    public function handle(FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('reference', $value)
                    ->orWhereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(RecurringBill::class);
        $queryBuilder->where('recurring_bills.fulfilment_customer_id', $parent->id);

        return $queryBuilder
            ->defaultSort('reference')
            ->allowedSorts(['reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(FulfilmentCustomer $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                    [
                        'title'       => __("You don't have any recurring bill yet").' 😭',
                        'count'       => $parent->number_recurring_bills
                    ]
                )
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $recurringBills): AnonymousResourceCollection
    {
        return RecurringBillsResource::collection($recurringBills);
    }

    public function htmlResponse(LengthAwarePaginator $recurringBills, ActionRequest $request): Response
    {
        return Inertia::render(
            'Billing/RetinaRecurringBills',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('recurring bills'),
                'model'     => __('Recurring Bill'),
                'pageHead'    => [
                    'title'         => __('recurring bills'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-receipt'],
                    ],
                ],
                'data' => RecurringBillsResource::collection($recurringBills)
            ]
        )->table(
            $this->tableStructure($this->parent)
        );
    }

    public function getBreadcrumbs(string $routeName): array
    {
        return match ($routeName) {
            'retina.billing.recurring.index' =>
            array_merge(
                ShowBillingDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'retina.billing.recurring.index',
                            ],
                            'label' => __('Recurring bills'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),
        };
    }

}