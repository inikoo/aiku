<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentDashboard extends OrgAction
{
    use WithDashboard;
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
                'dashboard_stats' => [
                    'widgets' => [
                        'column_count'    => 4,
                        'components' => [
                            $this->getWidget(
                                type: 'overview',
                                rowSpan: 2,
                                data: [
                                    'value' => 10289, // Example value
                                    'description' => __('Active Customers'),
                                    'type' => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_customers_status_inactive,
                                    'description'   => __('Inactive Customers'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                colSpan: 2,
                                data: [
                                    'value'         => $fulfilment->stats->number_pallet_deliveries,
                                    'description'   => __('Deliveries'),
                                    'type'          => 'number',
                                ],
                                visual: [
                                    'type' => 'MeterGroup',
                                    'value' => 382,
                                    'max' => 500,
                                    'color'=> 'bg-blue-500',
                                ], 
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallet_returns,
                                    'description'   => __('Returns'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallets,
                                    'description'   => __('Pallets'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallets_with_stored_items,
                                    'description'   => __('Pallets with items'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallets_type_pallet,
                                    'description'   => __('Pallets type pallet'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallets_type_box,
                                    'description'   => __('Pallets type box'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_pallets_type_oversize,
                                    'description'   => __('Pallets type oversize'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_stored_items,
                                    'description'   => __('Stored items'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_recurring_bills,
                                    'description'   => __('Recurring Bills'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_recurring_bills_status_current,
                                    'description'   => __('Current Recurring Bills'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->stats->number_recurring_bills_status_former,
                                    'description'   => __('Former Recurring Bills'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->shop->orderingStats->number_invoices,
                                    'description'   => __('Total Invoices'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->shop->orderingStats->number_unpaid_invoices,
                                    'description'   => __('Total Unpaid Invoices'),
                                    'type'          => 'number',
                                ]
                            ),

                            $this->getWidget(
                                data: [
                                    'value'         => $fulfilment->shop->orderingStats->unpaid_invoices_amount,
                                    'description'   => __('Amount Unpaid Invoices'),
                                    'type'          => 'currency',
                                    'status'        => $fulfilment->shop->orderingStats->unpaid_invoices_amount < 0 ? 'danger' : '',
                                    'currency_code' => $fulfilment->shop->currency->code,
                                ]
                            ),
                        ]
                    ]
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
