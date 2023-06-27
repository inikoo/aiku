<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 12:42:37 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\GuestTabsEnum;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\Auth\Guest;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGuest extends InertiaAction
{
    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $this->initialisation($request)->withTab(GuestTabsEnum::values());

        return $guest;
    }

    public function jsonResponse(Guest $guest): GuestResource
    {
        return new GuestResource($guest);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.users.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(Guest $guest, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/Guest',
            [
                'title'       => __('guest'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($guest, $request),
                    'next'     => $this->getNext($guest, $request),
                ],
                'pageHead'    => [
                    'title'     => $guest->contact_name,

                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => GuestTabsEnum::navigation()
                ],
                GuestTabsEnum::SHOWCASE->value => $this->tab == GuestTabsEnum::SHOWCASE->value ?
                    fn () => GetGuestShowcase::run($guest)
                    : Inertia::lazy(fn () => GetGuestShowcase::run($guest)),
                GuestTabsEnum::HISTORY->value => $this->tab == GuestTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($guest))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($guest)))
            ]
        )->table(IndexHistories::make()->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Guest $guest, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('guests')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $guest->slug,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'sysadmin.guests.show',
            'sysadmin.guests.edit' =>

            array_merge(
                SysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['guest'],
                    [
                        'index' => [
                            'name'       => 'sysadmin.guests.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'sysadmin.guests.show',
                            'parameters' => [$routeParameters['guest']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }

    public function getPrevious(Guest $guest, ActionRequest $request): ?array
    {
        $previous = Guest::where('slug', '<', $guest->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Guest $guest, ActionRequest $request): ?array
    {
        $next = Guest::where('slug', '>', $guest->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Guest $guest, string $routeName): ?array
    {
        if(!$guest) {
            return null;
        }
        return match ($routeName) {
            'sysadmin.guests.show'=> [
                'label'=> $guest->contact_name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'guest'=> $guest->slug
                    ]

                ]
            ]
        };
    }

}
