<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\SysAdmin\UserRequest\ShowUserRequestLogs;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\Http\Resources\UI\LoggedUserResource;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfile
{
    use AsAction;
    use WithInertia;
    use WithActionButtons;

    public function asController(ActionRequest $request): User
    {
        return $request->user();
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function htmlResponse(User $user, ActionRequest $request): Response
    {

        return Inertia::render(
            "Profile",
            [
            "title"       => __("Profile"),
            "breadcrumbs" => $this->getBreadcrumbs(),
            "pageHead"    => [
                "title" => __("My Profile"),
            ],
            'pageHead'    => [
                'title' => $user->contact_name,
                'meta'  => [
                        [
                            'label'     => $user->email,
                        'leftIcon'      => [
                            'icon'    => 'fal fa-id-card',
                            'tooltip' => __('Email')
                            ]
                        ],

                        ['label'        => $user->username,
                            'leftIcon'  => [
                                'icon'    => 'fal fa-user',
                                'tooltip' => __('User')
                            ]
                        ]
                ],
                'actions'     => [
                    $this->getEditActionIcon($request, null),
                ],
            ],
            'tabs'        => [
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
                fn () => UserRequestLogsResource::collection(ShowUserRequestLogs::run($user->username, ProfileTabsEnum::VISIT_LOGS->value))
                : Inertia::lazy(fn () => UserRequestLogsResource::collection(ShowUserRequestLogs::run($user->username, ProfileTabsEnum::VISIT_LOGS->value))),

            // ProfileTabsEnum::TODAY_TIMESHEETS->value => $this->tab == ProfileTabsEnum::TODAY_TIMESHEETS->value ?
            //     fn () => TimesheetsResource::collection(IndexTimesheets::run($user->parent, ProfileTabsEnum::TODAY_TIMESHEETS->value, true))
            //     : Inertia::lazy(fn () => TimesheetsResource::collection(IndexTimesheets::run($user->parent, ProfileTabsEnum::TODAY_TIMESHEETS->value, true))),

            'auth'          => [
                    'user' => LoggedUserResource::make($user)->getArray(),
                ],

        ]
        )
    ->table(IndexTimesheets::make()->tableStructure(modelOperations: [
            'createLink' => [
                [
                    'type'          => 'button',
                    'style'         => 'primary',
                    'icon'          => 'fal fa-file-export',
                    'id'            => 'pdf-export',
                    'label'         => 'Excel',
                    'key'           => 'action',
                    'target'        => '_blank',
                    // 'route'         => [
                    //     'name'       => 'grp.org.hr.employees.timesheets.export',
                    //     'parameters' => [
                    //         'organisation' => $employee->organisation->slug,
                    //         'employee'     => $employee->slug,
                    //         'type'         => 'xlsx'
                    //     ]
                    // ]
                ]
            ],
        ], prefix: ProfileTabsEnum::TIMESHEETS->value))
        ->table(ShowUserRequestLogs::make()->tableStructure())
        ->table(IndexHistory::make()->tableStructure());
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
