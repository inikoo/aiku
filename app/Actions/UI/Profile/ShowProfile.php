<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\GrpAction;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\SysAdmin\UserRequest\IndexUserRequestLogs;
use App\Actions\SysAdmin\UserRequest\ShowUserRequestLogs;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfile extends GrpAction
{
    use AsAction;
    use WithInertia;
    use WithActionButtons;

    public function asController(ActionRequest $request): User
    {
        $this->initialisation(group(), $request)->withTab(ProfileTabsEnum::values());

        return $request->user();
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function htmlResponse(User $user, ActionRequest $request): Response
    {
        /** @var Employee|Guest $parent */
        $parent = $user->parent;

        return Inertia::render(
            "Profile",
            [
                "title"                          => __("Profile"),
                "breadcrumbs"                    => $this->getBreadcrumbs(),
                "pageHead"                       => [
                    "title"        => __("My Profile"),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('edit profile'),
                            'route' => [
                                'name'       => 'grp.profile.edit',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'tabs'                           => [
                    'current'    => $this->tab,
                    'navigation' => ProfileTabsEnum::navigation()
                ],

                // pls fix the current tab
                ProfileTabsEnum::SHOWCASE->value => $this->tab == ProfileTabsEnum::SHOWCASE->value ?
                    fn () => GetProfileShowcase::run($user)
                    : Inertia::lazy(fn () => GetProfileShowcase::run($user)),

                ProfileTabsEnum::TIMESHEETS->value => $this->tab == ProfileTabsEnum::TIMESHEETS->value ?
                    fn () => TimesheetsResource::collection(IndexTimesheets::run($user->parent, ProfileTabsEnum::TIMESHEETS->value))
                    : Inertia::lazy(fn () => TimesheetsResource::collection(IndexTimesheets::run($user->parent, ProfileTabsEnum::TIMESHEETS->value))),

                ProfileTabsEnum::HISTORY->value => $this->tab == ProfileTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($user, ProfileTabsEnum::HISTORY->value))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($user, ProfileTabsEnum::HISTORY->value))),

                ProfileTabsEnum::VISIT_LOGS->value => $this->tab == ProfileTabsEnum::VISIT_LOGS->value ?
                    fn () => UserRequestLogsResource::collection(IndexUserRequestLogs::run())
                    : Inertia::lazy(fn () => UserRequestLogsResource::collection(IndexUserRequestLogs::run())),


                // 'auth'          => [
                //         'user' => LoggedUserResource::make($user)->getArray(),
                //     ],

            ]
        )
            ->table(
                IndexTimesheets::make()->tableStructure(
                    parent: $parent,
                    prefix: ProfileTabsEnum::TIMESHEETS->value
                )
            )
            ->table(ShowUserRequestLogs::make()->tableStructure())
            ->table(IndexHistory::make()->tableStructure(prefix: ProfileTabsEnum::HISTORY->value));
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(ShowDashboard::make()->getBreadcrumbs(), [
            [
                "type"   => "simple",
                "simple" => [
                    "route" => [
                        "name" => "grp.profile.show",
                    ],
                    "label" => __("my profile"),
                ],
            ],
        ]);
    }
}
