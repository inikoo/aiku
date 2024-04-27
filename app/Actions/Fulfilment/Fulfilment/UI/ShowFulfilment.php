<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 15:00:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Enums\UI\Fulfilment\FulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowFulfilment extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function handle(Fulfilment $fulfilment): Fulfilment
    {
        return $fulfilment;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {
        $this->initialisation($organisation, $request)->withTab(FulfilmentTabsEnum::values());

        return $this->handle($fulfilment);
    }

    private function getDashboard(Fulfilment $fulfilment): array
    {
        // dd($this->organisation->fulfilmentStats);

        return [
            'flatTreeMaps' => [


                [
                    [
                        'name'  => __('Products'),
                        'icon'  => ['fal', 'fa-cube'],
                        'index' => [
                            'number' => 0  // TODO
                        ],
                    ],
                    [
                        'name'  => __('Customers'),
                        'icon'  => ['fal', 'fa-user-tie'],
                        'index' => [
                            'number' => $fulfilment->shop->crmStats->number_customers
                        ],

                    ],
                    [
                        'name'  => __('Recurring bills'),
                        'icon'  => ['fal', 'fa-receipt'],
                        'index' => [
                            'number' => 0  // TODO
                        ],
                    ],
                    [
                        'name'  => __('Invoices'),
                        'icon'  => ['fal', 'fa-file-invoice-dollar'],
                        'index' => [
                            'number' => $fulfilment->shop->stats->number_invoices
                        ],
                    ],


                ],
                [
                    [
                        'name'  => __('Pallets'),
                        'icon'  => ['fal', 'fa-pallet'],
                        'index' => [
                            'number' => $this->organisation->fulfilmentStats->number_pallets_status_storing
                        ],
                    ],
                    [
                        'name'  => __('Stored Items'),
                        'icon'  => ['fal', 'fa-narwhal'],
                        'index' => [
                            'number' => $this->organisation->fulfilmentStats->number_stored_items
                        ],
                    ],

                ],
                [
                    [
                        'name'  => __('Deliveries'),
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'index' => [
                            'number' => $fulfilment->stats->number_pallet_deliveries
                        ],
                    ],
                    [
                        'name'  => __('Returns'),
                        'icon'  => ['fal', 'fa-sign-out'],
                        'index' => [
                            'number' => $this->organisation->fulfilmentStats->number_pallet_returns
                        ],
                    ],



                ],
            ],
            'scheduledActivities' => [
                [
                    'icon'          => 'fal fa-pallet',
                    'title'         => __('pallets  '),
                    'description'   => (
                        $this->organisation->fulfilmentStats->number_pallets_state_in_process
                        + $this->organisation->fulfilmentStats->number_pallets_state_submitted
                        + $this->organisation->fulfilmentStats->number_pallets_state_confirmed
                    ) . ' ' . __('pending')
                ],
                [
                    'icon'          => 'fal fa-truck-couch',
                    'title'         => __('pallet delivery'),
                    'description'   => (
                        $this->organisation->fulfilmentStats->number_pallet_deliveries_state_in_process
                        + $this->organisation->fulfilmentStats->number_pallet_deliveries_state_submitted
                        + $this->organisation->fulfilmentStats->number_pallet_deliveries_state_confirmed
                    ) . ' ' . __('pending')
                ],
                [
                    'icon'          => 'fal fa-sign-out',
                    'title'         => __('pallet returns'),
                    'description'   => (
                        $this->organisation->fulfilmentStats->number_pallet_returns_state_in_process
                        + $this->organisation->fulfilmentStats->number_pallet_returns_state_submitted
                        + $this->organisation->fulfilmentStats->number_pallet_returns_state_confirmed
                        + $this->organisation->fulfilmentStats->number_pallet_returns_state_picking
                        + $this->organisation->fulfilmentStats->number_pallet_returns_state_picked
                    ) . ' ' . __('pending')
                ],
            ]
        ];
    }

    public function htmlResponse(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Fulfilment',
            [
                'title'       => __('fulfilment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($fulfilment, $request),
                    'next'     => $this->getNext($fulfilment, $request),
                ],
                'pageHead'    => [
                    'title' => $fulfilment->shop->name,
                    'icon'  => [
                        'title' => __('Fulfilment'),
                        'icon'  => 'fal fa-pallet-alt'
                    ],

                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentTabsEnum::navigation()
                ],

                FulfilmentTabsEnum::DASHBOARD->value => $this->tab == FulfilmentTabsEnum::DASHBOARD->value
                    ?
                    fn () => $this->getDashboard($fulfilment)
                    : Inertia::lazy(fn () => $this->getDashboard($fulfilment)),



            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    public function jsonResponse(Fulfilment $fulfilment): FulfilmentResource
    {
        return new FulfilmentResource($fulfilment);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {


        $fulfilment = Fulfilment::where('slug', Arr::get($routeParameters, 'fulfilment'))->first();

        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.index',
                                    'parameters' => Arr::only($routeParameters, 'organisation')
                                ],
                                'label' => __('fulfilment'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                                    'parameters' => $routeParameters
                                ],
                                'label' => $fulfilment?->shop?->name,
                                'icon'  => 'fal fa-bars'
                            ]

                        ],
                        'suffix'         => $suffix,
                    ]
                ]
            );
    }

    public function getPrevious(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $previous = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '<', $fulfilment->shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->fulfilment, $request->route()->getName());
    }

    public function getNext(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $next = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '>', $fulfilment->shop->code)->orderBy('code')->first();

        return $this->getNavigation($next?->fulfilment, $request->route()->getName());
    }

    private function getNavigation(?Fulfilment $fulfilment, string $routeName): ?array
    {
        if (!$fulfilment) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.operations.dashboard' => [
                'label' => $fulfilment->shop?->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'fulfilment'   => $fulfilment->slug
                    ]

                ]
            ]
        };
    }
}
