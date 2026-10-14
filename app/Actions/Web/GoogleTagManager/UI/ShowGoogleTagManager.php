<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\GoogleTagManager\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithShopPpcPermissions;
use App\Actions\UI\Websites\WebsitesDashboard;
use App\Models\SysAdmin\Group;
use App\Models\Web\GoogleTagManagerContainer;
use App\Services\GoogleTagManager\GoogleTagManagerClient;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * Every Google Tag Manager account and container of the group on one page, read from the copy
 * `google_tag_manager_containers` keeps; nothing here calls Google.
 */
class ShowGoogleTagManager extends OrgAction
{
    use WithShopPpcPermissions;

    public function authorize(ActionRequest $request): bool
    {
        return $this->canViewGroupSeo($request->user());
    }

    public function asController(ActionRequest $request): Group
    {
        $this->initialisationFromGroup(group(), $request);

        return group();
    }

    public function htmlResponse(Group $group, ActionRequest $request): Response
    {
        $title = __('Google Tag Manager');

        $containers = GoogleTagManagerContainer::where('group_id', $group->id)
            ->orderBy('account_name')
            ->orderBy('name')
            ->get();

        return Inertia::render(
            'Org/Web/GoogleTagManager',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => $title,
                'pageHead'    => [
                    'title' => $title,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-tags'],
                        'title' => $title,
                    ],
                ],
                'data'        => [
                    'is_connected'   => GoogleTagManagerClient::isConnected($group),
                    'connected_at'   => Arr::get($group->settings, 'google_tag_manager.connected_at'),
                    'fetched_at'     => $containers->max('fetched_at'),
                    'can_connect'    => $this->isGroupAdmin($request->user()),
                    'connect_route'  => [
                        'name'       => 'grp.websites.google_tag_manager.connect',
                        'parameters' => [],
                    ],
                    'accounts'       => $containers->groupBy('account_id')->map(fn ($rows) => [
                        'account_id'   => $rows->first()->account_id,
                        'account_name' => $rows->first()->account_name,
                        'containers'   => $rows->map(fn (GoogleTagManagerContainer $container) => [
                            'id'              => $container->id,
                            'name'            => $container->name,
                            'public_id'       => $container->public_id,
                            'usage_context'   => $container->usage_context,
                            'domain_names'    => $container->domain_names ?? [],
                            'tag_manager_url' => $container->tag_manager_url,
                        ])->values(),
                    ])->values(),
                ],
            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...WebsitesDashboard::make()->getBreadcrumbs(),
            [
                'type'   => 'simple',
                'simple' => [
                    'route' => ['name' => 'grp.websites.google_tag_manager.index'],
                    'label' => __('Google Tag Manager'),
                ],
            ],
        ];
    }
}
