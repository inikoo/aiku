<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 03:25:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp;

use App\Actions\GrpAction;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\UI\Group\GrpTabsEnum;
use App\Enums\UI\Organisation\OrgTabsEnum;
use App\Enums\UI\SysAdmin\UserTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Http\Resources\SysAdmin\Organisation\OrganisationResource;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGroup extends GrpAction
{
    
    public function handle(): Group
    {
        $group = group();

        return $group;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('sysadmin.edit');
    }

    public function asController(ActionRequest $request): Group
    {
        $this->initialisation(group(), $request)->withTab(GrpTabsEnum::values());
        return $this->handle(group());
    }


    public function htmlResponse(Group $group, ActionRequest $request): Response
    {
        $group = group();
        return Inertia::render(
            'Group',
            [
                'breadcrumbs' => $this->getBreadcrumbs( $request->route()->originalParameters()),
                'title'       => __('group'),
                'pageHead'    => [
                    'model'   => __('group'),
                    'icon'   => ['fal', 'fa-city'],
                    'title'   => $group->name,
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => GrpTabsEnum::navigation()
                ],
                GrpTabsEnum::SHOWCASE->value => $this->tab == OrgTabsEnum::SHOWCASE->value ?
                fn () => GroupResource::make($group)
                : Inertia::lazy(fn () => GroupResource::make($group)),

                GrpTabsEnum::HISTORY->value => $this->tab == OrgTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($group))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($group)))



            ]
        )->table(IndexHistory::make()->tableStructure(prefix: GrpTabsEnum::HISTORY->value));
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {

        $group = group();
        return array_merge(
            ShowDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.index',
                            ],
                            'label' => __("Groups"),
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.show',
                            ],
                            'label' => $group->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }
}
