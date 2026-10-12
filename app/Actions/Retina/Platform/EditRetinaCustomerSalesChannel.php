<?php

/*
 * author Arya Permana - Kirin
 * created on 01-07-2025-11h-01m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Platform;

use App\Actions\Dropshipping\CustomerSalesChannel\UI\WithCustomerSalesChannelSettingsBlueprint;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithRetinaCustomerOwnedRouteModels;
use App\Models\Dropshipping\CustomerSalesChannel;
use App\Models\Dropshipping\EbayUser;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditRetinaCustomerSalesChannel extends RetinaAction
{
    use WithRetinaCustomerOwnedRouteModels;
    use WithCustomerSalesChannelSettingsBlueprint;

    public function handle(CustomerSalesChannel $customerSalesChannel, ActionRequest $request): Response
    {
        $routeName = $customerSalesChannel->user instanceof EbayUser
            ? 'retina.models.customer_sales_channel.ebay_update'
            : 'retina.models.customer_sales_channel.update';

        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $customerSalesChannel
                ),
                'title' => __('Edit sales channel'),
                'pageHead' => [
                    'title' => __('Edit sales channel'),
                    'icon' => [
                        'icon' => ['fal', 'fa-code-branch'],
                        'title' => __('Sales channel')
                    ],
                    'actions' => [
                        [
                            'type' => 'button',
                            'style' => 'exitEdit',
                            'label' => __('Exit edit'),
                            'route' => [
                                'name' => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => $this->getCustomerSalesChannelSettingsBlueprint($customerSalesChannel),
                    'args' => [
                        'updateRoute' => [
                            'name' => $routeName,
                            'parameters' => [
                                'customerSalesChannel' => $customerSalesChannel->id
                            ],
                            'method' => 'patch'
                        ]
                    ]
                ]
            ]
        );
    }

    public function asController(CustomerSalesChannel $customerSalesChannel, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($customerSalesChannel, $request);
    }

    public function getBreadcrumbs(CustomerSalesChannel $customerSalesChannel): array
    {
        return array_merge(
            ShowRetinaCustomerSalesChannelDashboard::make()->getBreadcrumbs(
                $customerSalesChannel,
            ),
            [
                [
                    'type' => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Editing Channel'),
                    ]
                ]
            ]
        );
    }
}
