<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 15:00:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\OMS\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Enums\UI\CustomerTabsEnum;
use App\Enums\UI\FulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentResource;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
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

    public function htmlResponse(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Fulfilment',
            [
                'title'        => __('fulfilment'),
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($fulfilment, $request),
                    'next'     => $this->getNext($fulfilment, $request),
                ],
                'pageHead'     => [
                    'title'   => $fulfilment->shop->name,
                    'icon'    => [
                        'title' => __('Fulfilment'),
                        'icon'  => 'fal fa-pallet-alt'
                    ],

                ],

                'tabs'         => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentTabsEnum::navigation()
                ],

                FulfilmentTabsEnum::PALLETS->value => $this->tab == FulfilmentTabsEnum::PALLETS->value ?
                    fn () => PalletResource::collection(IndexPallets::run($fulfilment->organisation, FulfilmentTabsEnum::PALLETS->value))
                    : Inertia::lazy(fn () => PalletResource::collection(IndexPallets::run($fulfilment->organisation, FulfilmentTabsEnum::PALLETS->value))),

            ]
        )->table(IndexPallets::make()->tableStructure(prefix: FulfilmentTabsEnum::PALLETS->value));
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

        $fulfilment=Fulfilment::where('slug', $routeParameters['fulfilment'])->first();

        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilment.index',
                                    'parameters' => $routeParameters
                                ],
                                'label' => __('fulfilment'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilment.show',
                                    'parameters' => $routeParameters
                                ],
                                'label' => $fulfilment->shop->code,
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
        $previous = Shop::where('type', ShopTypeEnum::FULFILMENT)->where('code', '<', $fulfilment->shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->fulfilment, $request->route()->getName());
    }

    public function getNext(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $next = Shop::where('type', ShopTypeEnum::FULFILMENT)->where('code', '>', $fulfilment->shop->code)->orderBy('code')->first();

        return $this->getNavigation($next?->fulfilment, $request->route()->getName());
    }

    private function getNavigation(?Fulfilment $fulfilment, string $routeName): ?array
    {
        if (!$fulfilment) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilment.show' => [
                'label' => $fulfilment->shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'      => $this->organisation->slug,
                        'fulfilment'        => $fulfilment->slug
                    ]

                ]
            ]
        };
    }
}
