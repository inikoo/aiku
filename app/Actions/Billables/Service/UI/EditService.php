<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Service\UI;

use App\Actions\OrgAction;
use App\Enums\Billables\Service\ServiceEditTypeEnum;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Models\Billables\Service;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditService extends OrgAction
{
    public function handle(Service $service): Service
    {
        return $service;
    }

    public function authorize(ActionRequest $request): bool
    {
        // dd($this->shop);
        $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    // public function inOrganisation(Asset $product, ActionRequest $request): Asset
    // {
    //     $this->initialisation($request);

    //     return $this->handle($product);
    // }

    // /** @noinspection PhpUnusedParameterInspection */
    // public function inShop(Shop $shop, Asset $product, ActionRequest $request): Asset
    // {
    //     $this->initialisation($request);

    //     return $this->handle($product);
    // }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Service $service, ActionRequest $request): Service
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($service);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(Service $service, ActionRequest $request): Response
    {


        if ($service->edit_type == ServiceEditTypeEnum::QUANTITY) {
            $fixedPrice = true;
            $disableNet = false;
        } elseif ($service->edit_type == ServiceEditTypeEnum::NET) {
            $fixedPrice = false;
            $disableNet = true;
        }

        if ($service->status == false || $service->state == ServiceStateEnum::DISCONTINUED) {
            $active = false;
        } else {
            $active = true;
        }
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('service'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($service, $request),
                    'next'     => $this->getNext($service, $request),
                ],
                'pageHead'    => [
                    'title'    => $service->code,
                    'icon'     =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('product')
                        ],
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('Edit Service'),
                            'fields' => [
                                'in_public' => [
                                    'type'    => 'toggle',
                                    'label'   => __('public'),
                                    'value'   => $service->is_public
                                ],
                                'active' => [
                                    'type'    => 'toggle',
                                    'label'   => __('active'),
                                    'value'   => $active
                                ],
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => $service->code,
                                    'readonly' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('label'),
                                    'value'    => $service->name,
                                    'readonly' => true
                                ],
                                'description' => [
                                    'type'  => 'input',
                                    'label' => __('description'),
                                    'value' => $service->description
                                ],
                                'unit' => [
                                    'type'     => 'input',
                                    'label'    => __('unit'),
                                    'value'    => $service->unit,
                                    'readonly' => true
                                ],
                                'fixed_price' => [
                                    'type'    => 'toggle',
                                    'label'   => __('fixed price'),
                                    'value'   => $fixedPrice
                                ],
                                'price' => [
                                    'type'    => 'input',
                                    'label'   => __('price'),
                                    'value'   => $service->price,
                                    'hidden' => $disableNet
                                ],

                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.services.update',
                            'parameters' => [
                                'service' => $service->id
                            ]

                        ],
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowService::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(Service $service, ActionRequest $request): ?array
    {
        $previous = Service::where('slug', '<', $service->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Service $service, ActionRequest $request): ?array
    {
        $next = Service::where('slug', '>', $service->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Service $service, string $routeName): ?array
    {
        if (!$service) {
            return null;
        }
        return match ($routeName) {
            'shops.products.edit' => [
                'label' => $service->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'product' => $service->slug
                    ]

                ]
            ],
            'shops.show.products.edit' => [
                'label' => $service->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'shop'   => $service->shop->slug,
                        'product' => $service->slug
                    ]

                ]
            ],
            default => null,
        };
    }
}
