<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Enums\UI\Fulfilment\RecurringBillsTabsEnum;
use App\Http\Resources\Fulfilment\RecurringBillsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RecurringBill;
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

class IndexRecurringBills extends OrgAction
{
    use WithFulfilmentCustomerSubNavigation;

    private Fulfilment|FulfilmentCustomer $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;

        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillsTabsEnum::values());

        return $this->handle($fulfilment, RecurringBillsTabsEnum::RECURRING_BILLS->value);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillsTabsEnum::values());

        return $this->handle($fulfilmentCustomer, RecurringBillsTabsEnum::RECURRING_BILLS->value);
    }

    public function handle(Fulfilment|FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
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

        if ($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('recurring_bills.fulfilment_customer_id', $parent->id);
        } else {
            $queryBuilder->where('recurring_bills.fulfilment_id', $parent->id);
        }
        $queryBuilder->join('fulfilment_customers', 'recurring_bills.fulfilment_customer_id', '=', 'fulfilment_customers.id');
        $queryBuilder->join('customers', 'fulfilment_customers.customer_id', '=', 'customers.id');
        $queryBuilder->join('currencies', 'recurring_bills.currency_id', '=', 'currencies.id');

        return $queryBuilder
            ->defaultSort('reference')
            ->select([
                'recurring_bills.id',
                'recurring_bills.slug',
                'recurring_bills.reference',
                'recurring_bills.start_date',
                'recurring_bills.end_date',
                'recurring_bills.net_amount',
                'fulfilment_customers.slug as fulfilment_customer_slug',
                'customers.name as customer_name',
                'currencies.code as currency_code',
                'currencies.symbol as currency_symbol',
            ])
            ->allowedSorts(['reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment|FulfilmentCustomer $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                    match(class_basename($parent)) {
                        'Fulfilment' => [
                            'title'       => __("You don't have any recurring bill yet").' 😭',
                            'description' => __("Dont worry soon you will be pretty busy"),
                            'count'       => $parent->stats->number_recurring_bills,

                        ],
                        'FulfilmentCustomer' => [
                            'title'       => __("This customer don't have any recurring bill yet").' 😭',
                            'count'       => $parent->number_recurring_bills
                        ]
                    }
                );
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Fulfilment) {
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, sortable: true, searchable: true);
            };
            $table->column(key: 'net_amount', label: __('net'), canBeHidden: false, sortable: true, searchable: true)
            ->column(key: 'start_date', label: __('start date'), canBeHidden: false, sortable: true, searchable: true)
            ->column(key: 'end_date', label: __('end date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $recurringBills): AnonymousResourceCollection
    {
        return RecurringBillsResource::collection($recurringBills);
    }

    public function htmlResponse(LengthAwarePaginator $recurringBills, ActionRequest $request): Response
    {
        $subNavigation=[];

        if($this->parent instanceof  FulfilmentCustomer) {
            $subNavigation=$this->getFulfilmentCustomerSubNavigation($this->parent, $request);
        }

        return Inertia::render(
            'Org/Fulfilment/RecurringBills',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('recurring bills'),
                'pageHead'    => [
                    'title'         => __('recurring bills'),
                    'subNavigation' => $subNavigation,
                    'icon'          => [
                        'icon'  => ['fal', 'fa-receipt'],
                    ],
                ],

                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => RecurringBillsTabsEnum::navigation()
                ],

                RecurringBillsTabsEnum::RECURRING_BILLS->value => $this->tab == RecurringBillsTabsEnum::RECURRING_BILLS->value ?
                    fn () => RecurringBillsResource::collection($recurringBills)
                    : Inertia::lazy(fn () => RecurringBillsResource::collection($recurringBills)),


            ]
        )->table(
            $this->tableStructure(
                parent: $this->parent,
                prefix: RecurringBillsTabsEnum::RECURRING_BILLS->value
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Recurring bills'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {

            'grp.org.fulfilments.show.operations.recurring_bills.index'=> array_merge(
                ShowFulfilment::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),

            'grp.org.fulfilments.show.crm.customers.show.recurring_bills.index'=> array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','fulfilment','fulfilmentCustomer'])
                    ]
                )
            ),
        };
    }
}
