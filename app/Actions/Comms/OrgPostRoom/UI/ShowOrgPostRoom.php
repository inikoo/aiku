<?php

/*
 * author Arya Permana - Kirin
 * created on 02-12-2024-10h-07m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\OrgPostRoom\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Mail\OrgPostRoomResource;
use App\Models\Catalogue\Shop;
use App\Models\Comms\OrgPostRoom;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property OrgPostRoom $orgPostRoom
 */
class ShowOrgPostRoom extends OrgAction
{
    public function handle(OrgPostRoom $orgPostRoom): OrgPostRoom
    {
        return $orgPostRoom;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasAnyPermission([
            'shop-admin.'.$this->shop->id,
            'marketing.'.$this->shop->id.'.view',
            'web.'.$this->shop->id.'.view',
            'orders.'.$this->shop->id.'.view',
            'crm.'.$this->shop->id.'.view',
        ]);
    }

    public function inShop(Organisation $organisation, Shop $shop, OrgPostRoom $orgPostRoom, ActionRequest $request): OrgPostRoom
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($orgPostRoom);
    }

    public function htmlResponse(OrgPostRoom $orgPostRoom, ActionRequest $request): Response
    {

        return Inertia::render(
            'Mail/PostRoom',
            [
                'title'       => __('post room'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($orgPostRoom, $request),
                    'next'     => $this->getNext($orgPostRoom, $request),
                ],
                'pageHead'    => [
                    'icon'  => 'fal fa-cash-register',
                    'title' => $orgPostRoom->name,

                ],
                'data'   => OrgPostRoomResource::make($orgPostRoom)
            ]
        );
    }


    public function jsonResponse(OrgPostRoom $orgPostRoom): OrgPostRoomResource
    {
        return new OrgPostRoomResource($orgPostRoom);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (OrgPostRoom $orgPostRoom, array $routeParameters, $suffix = null) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => $orgPostRoom->name,
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $orgPostRoom = OrgPostRoom::where('slug', $routeParameters['orgPostRoom'])->first();

        return match ($routeName) {
            'grp.org.shops.show.comms.post-rooms.show' =>
            array_merge(
                IndexOrgPostRoom::make()->getBreadcrumbs('grp.org.shops.show.comms.post-rooms.index', $routeParameters),
                $headCrumb(
                    $orgPostRoom,
                    [

                        'name'       => 'grp.org.shops.show.comms.post-rooms.show',
                        'parameters' => $routeParameters

                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(OrgPostRoom $orgPostRoom, ActionRequest $request): ?array
    {
        $previous = OrgPostRoom::where('slug', '<', $orgPostRoom->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(OrgPostRoom $orgPostRoom, ActionRequest $request): ?array
    {
        $next = OrgPostRoom::where('slug', '>', $orgPostRoom->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?OrgPostRoom $orgPostRoom, string $routeName): ?array
    {
        if (!$orgPostRoom) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.comms.post-rooms.show' => [
                'label' => $orgPostRoom->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'shop'         => $this->shop->slug,
                        'orgPostRoom'  => $orgPostRoom->slug
                    ]

                ]
            ],
        };
    }
}