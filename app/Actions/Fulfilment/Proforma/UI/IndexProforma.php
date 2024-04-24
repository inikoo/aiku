<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 20:05:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Proforma\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\FulfilmentCustomersResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\FulfilmentProforma;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexProforma extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.view");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }


    public function handle(Fulfilment|FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('number', $value)
                    ->orWhereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(FulfilmentProforma::class);

        if($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('fulfilment_customer_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('slug')
            ->allowedSorts(['number', 'slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment $fulfilment, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($fulfilment, $modelOperations, $prefix) {
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
                        'title'       => __("You don't have any proforma yet").' 😭',
                        'description' => __("Dont worry soon you will be pretty busy"),
                        'count'       => $fulfilment->shop->crmStats->number_customers,
                        'action'      => [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new proforma'),
                            'label'   => __('proforma'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.create',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ]
                        ]
                    ]
                )
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'slug', label: __('slug'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return FulfilmentCustomersResource::collection($customers);
    }

    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {
        $container = [
            'icon'    => ['fal', 'fa-hand-holding-box'],
            'tooltip' => __('Fulfilment Shop'),
            'label'   => Str::possessive($this->fulfilment->shop->name)

        ];

        return Inertia::render(
            'Org/Fulfilment/Proformas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title'     => __('customers'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('customer')
                    ],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('New Customer'),
                            'label'   => __('New Customer'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.create',
                                'parameters' => [
                                    $this->fulfilment->organisation->slug,
                                    $this->fulfilment->slug
                                ]
                            ]
                        ],
                    ],
                ],
                'data'        => FulfilmentCustomersResource::collection($customers),

            ]
        )->table($this->tableStructure($this->fulfilment));
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('customers'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };


        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(
                $routeParameters
            ),
            $headCrumb(
                [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                    'parameters' => $routeParameters
                ]
            )
        );
    }
}
