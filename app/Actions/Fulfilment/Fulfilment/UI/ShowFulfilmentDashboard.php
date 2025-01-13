<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentDashboard extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->organisation->id}.view");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    public function handle(Fulfilment $fulfilment): Fulfilment
    {
        return $fulfilment;
    }


    public function htmlResponse(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/FulfilmentDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'        => __('fulfilment'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-hand-holding-box'],
                        'title' => __('fulfilment')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('fulfilment')
                    ],
                    'title' => __('fulfilment central command'),
                ],
                'stats' => [
                    'customers' => [
                        'active' => [
                            'label' => __('Active Customers'),
                            'count' => $fulfilment->stats->number_customers_status_active
                        ],
                        'inactive' => [
                            'label' => __('Inactive Customers'),
                            'count' => $fulfilment->stats->number_customers_status_inactive
                        ]
                        ],
                    'pallet_deliveries' => [
                        'label' => __('Deliveries'),
                        'count' => $fulfilment->stats->number_pallet_deliveries
                    ],
                    'pallet_returns' => [
                        'label' => __('Returns'),
                        'count' => $fulfilment->stats->number_pallet_returns
                    ],
                    'pallets' => [
                        'all'   => [
                            'label' => __('Pallets'),
                            'count' => $fulfilment->stats->number_pallets
                        ],
                        'pallets_with_stored_items' => [
                            'label' => __('Pallets with items'),
                            'count' => $fulfilment->stats->number_pallets_with_stored_items
                        ],
                        'pallets_type_pallet' => [
                            'label' => __('Pallets type pallet'),
                            'count' => $fulfilment->stats->number_pallets_type_pallet
                        ],
                        'pallets_type_box' => [
                            'label' => __('Pallets type box'),
                            'count' => $fulfilment->stats->number_pallets_type_box
                        ],
                        'pallets_type_oversize' => [
                            'label' => __('Pallets type oversize'),
                            'count' => $fulfilment->stats->number_pallets_type_oversize
                        ],
                    ],
                    'stored_items' => [
                        'label' => __('Stored items'),
                        'count' => $fulfilment->stats->number_stored_items
                    ],
                    'recurring_bills' => [
                        'all'     => [
                            'label' => __('Recurring Bills'),
                            'count' => $fulfilment->stats->number_recurring_bills
                        ],
                        'current' => [
                            'label' => __('Current Recurring Bills'),
                            'count' => $fulfilment->stats->number_recurring_bills_status_current
                        ],
                        'former' => [
                            'label' => __('Former Recurring Bills'),
                            'count' => $fulfilment->stats->number_recurring_bills_status_former
                        ],
                    ],
                    'ordering' => [
                        'total_invoices'     => [
                            'label' => __('Total Invoices'),
                            'count' => $fulfilment->shop->orderingStats->number_invoices
                        ],
                        'total_unpaid_invoices' => [
                            'label' => __('Total Unpaid Invoices'),
                            'count' => $fulfilment->shop->orderingStats->number_unpaid_invoices
                        ],
                        'amount_unpaid_invoices' => [
                            'label' => __('Amount Unpaid Invoices'),
                            'count' => $fulfilment->shop->orderingStats->unpaid_invoices_amount_org_currency
                        ],
                    ],
                ]


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Dashboard'),
                            'icon'  => 'fal fa-chart-network'
                        ]
                    ]
                ]
            );

    }


}
