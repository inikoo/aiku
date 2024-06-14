<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditFulfilmentCustomer extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomer
    {
        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): FulfilmentCustomer
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function htmlResponse(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($fulfilmentCustomer, $request),
                    'next'     => $this->getNext($fulfilmentCustomer, $request),
                ],
                'pageHead'    => [
                    'title'    => $fulfilmentCustomer->customer->name,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters()),
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('contact information'),
                            'label'  => __('contact'),
                            'fields' => [

                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
                                    'value' => $fulfilmentCustomer->customer->contact_name
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company'),
                                    'value' => $fulfilmentCustomer->customer->company_name
                                ],
                                'email'        => [
                                    'type'  => 'input',
                                    'label' => __('Email'),
                                    'value' => $fulfilmentCustomer->customer->email
                                ],
                                'phone'        => [
                                    'type'  => 'phone',
                                    'label' => __('Phone'),
                                    'value' => $fulfilmentCustomer->customer->phone
                                ],

                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.fulfilment-customer.update',
                            'parameters' => [$fulfilmentCustomer->id]
                        ],
                    ]

                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowFulfilmentCustomer::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
        );
    }

    public function getPrevious(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $previous = FulfilmentCustomer::where('slug', '<', $fulfilmentCustomer->slug)->when(true, function ($query) use ($fulfilmentCustomer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $fulfilmentCustomer->shop_id);
            }
        })->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $next = FulfilmentCustomer::where('slug', '>', $fulfilmentCustomer->slug)->when(true, function ($query) use ($fulfilmentCustomer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $fulfilmentCustomer->shop_id);
            }
        })->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?FulfilmentCustomer $fulfilmentCustomer, string $routeName): ?array
    {
        if (!$fulfilmentCustomer) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.edit' => [
                'label' => $fulfilmentCustomer->customer->contact_name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $fulfilmentCustomer->organisation->slug,
                        'fulfilment'         => $fulfilmentCustomer->fulfilment->slug,
                        'fulfilmentCustomer' => $fulfilmentCustomer->slug
                    ]
                ]
            ]
        };
    }
}
