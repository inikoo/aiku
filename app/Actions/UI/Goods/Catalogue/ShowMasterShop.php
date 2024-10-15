<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\UI\Goods\Catalogue;

use App\Actions\GrpAction;
use App\Enums\UI\Catalogue\MasterShopTabsEnum;
use App\Http\Resources\Goods\Catalogue\MasterShopResource;
use App\Models\Catalogue\MasterShop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMasterShop extends GrpAction
{
    use WithMasterCatalogueSubnavigation;

    public function handle(MasterShop $masterShop): MasterShop
    {
        return $masterShop;
    }

    public function asController(MasterShop $masterShop, ActionRequest $request): MasterShop
    {
        $group = group();
        $this->initialisation($group, $request)->withTab(MasterShopTabsEnum::values());
        return $this->handle($masterShop);
    }

    public function htmlResponse(MasterShop $masterShop,  ActionRequest $request): Response
    {
        $subNavigation = $this->getMasterShopNavigation($masterShop);

        return Inertia::render(
            'Org/Catalogue/Shop',
            [
                'title'        => __('Master shop'),
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($masterShop, $request),
                    'next'     => $this->getNext($masterShop, $request),
                ],

                'pageHead'     => [
                    'title'   => $masterShop->name,
                    'icon'    => [
                        'title' => __('Master shop'),
                        'icon'  => 'fal fa-store-alt'
                    ],
                    'subNavigation' => $subNavigation,
                    // 'actions' => [
                    //     $this->canEdit ? [
                    //         'type'  => 'button',
                    //         'style' => 'edit',
                    //         'label' => __('settings'),
                    //         'icon'  => 'fal fa-sliders-h',
                    //         'route' => [
                    //             'name'       => 'grp.org.shops.show.settings.edit',
                    //             'parameters' => $request->route()->originalParameters()
                    //         ]
                    //     ] : false,

                    // ]
                ],
                'tabs'         => [
                    'current'    => $this->tab,
                    'navigation' => MasterShopTabsEnum::navigation()
                ],

                MasterShopTabsEnum::SHOWCASE->value => $this->tab == MasterShopTabsEnum::SHOWCASE->value
                    ?
                    fn () => MasterShopResource::make($masterShop)
                    : Inertia::lazy(fn () => MasterShopResource::make($masterShop)),
            ]
        );
    }

    public function jsonResponse(MasterShop $masterShop): MasterShopResource
    {
        return new MasterShopResource($masterShop);
    }

    public function getBreadcrumbs($routeName, array $routeParameters, $suffix = null): array
    {

        $masterShop = MasterShop::where('slug', $routeParameters['masterShop'])->first();

        return
            array_merge(
                IndexMasterShops::make()->getBreadcrumbs($routeName, $routeParameters),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name'       => 'grp.goods.catalogue.shops.index',
                                    'parameters' => []
                                ],
                                'label' => __('Master shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.goods.catalogue.shops.show',
                                    'parameters' => [
                                        'masterShop' => $masterShop->slug
                                    ]
                                ],
                                'label' => $masterShop->code,
                                'icon'  => 'fal fa-store-alt'
                            ]


                        ],
                        'suffix'         => $suffix,
                    ]
                ]
            );

    }

    public function getPrevious(MasterShop $masterShop, ActionRequest $request): ?array
    {
        $previous = MasterShop::where('code', '<', $masterShop->code)->where('group_id', $this->group->id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(MasterShop $masterShop, ActionRequest $request): ?array
    {
        $next = MasterShop::where('code', '>', $masterShop->code)->where('group_id', $this->group->id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?MasterShop $masterShop, string $routeName): ?array
    {
        if (!$masterShop) {
            return null;
        }

        return match ($routeName) {
            'grp.goods.catalogue.shops.show' => [
                'label' => $masterShop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'masterShop'        => $masterShop->slug
                    ]

                ]
            ]
        };
    }

}