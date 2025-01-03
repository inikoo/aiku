<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\InertiaAction;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Catalogue\Shop;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Catalogue\ShopResource;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use App\Enums\Production\Production\ProductionStateEnum;
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditUser extends InertiaAction
{
    private User $user;

    public function handle(User $user): User
    {
        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->user = $user;

        $this->initialisation($request);

        return $this->handle($user);
    }


    public function htmlResponse(User $user, ActionRequest $request): Response
    {

        $orgTypeShop = [];

        $roles       = collect(RolesEnum::cases());
        $permissions = $roles->map(function ($role) {
            return [$role->label() => match ($role->scope()) {
                class_basename(Group::class) => Group::all()->map(function (Group $group) {
                    return [$group->name => [
                        'organisations' => $group->organisations->pluck('slug')
                    ]];
                }),
                class_basename(Organisation::class) => [
                    'organisations' => Organisation::all()->pluck('slug')
                ],
                class_basename(Shop::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'shops' => $organisation->shops->pluck('slug')
                    ]];
                }),
                class_basename(Fulfilment::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'fulfilments' => $organisation->fulfilments->pluck('slug')
                    ]];
                }),
                class_basename(Warehouse::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'warehouses' => $organisation->warehouses->pluck('slug')
                    ]];
                }),
                default => []
            }];
        });


        $organisations = $user->group->organisations;
        $reviewData    = $organisations->mapWithKeys(function ($organisation) {
            return [$organisation->slug => [
                'number_job_positions' => $organisation->humanResourcesStats->number_job_positions,
                'job_positions'        => $organisation->jobPositions->mapWithKeys(function ($jobPosition) {
                    return [$jobPosition->slug => [
                        'job_position'        => $jobPosition->name,
                        'number_roles'        => $jobPosition->stats->number_roles
                    ]];
                })
            ]];
        })->toArray();

        $organisationList = OrganisationsResource::collection($organisations);

        // dd($user);

        return Inertia::render("EditModel", [
            "title"       => __("user"),
            "breadcrumbs" => $this->getBreadcrumbs(
                $request->route()->getName(),
                $request->route()->originalParameters()
            ),
            "pageHead" => [
                "title"   => $user->username,
                "actions" => [
                    [
                        "type"  => "button",
                        "style" => "exitEdit",
                        "route" => [
                            "name"       => preg_replace('/edit$/', "show", $request->route()->getName()),
                            "parameters" => array_values($request->route()->originalParameters()),
                        ],
                    ],
                ],
            ],


            "formData" => [
                "blueprint" => [
                    [
                        "label"   => __("Profile Information"),
                        "title"   => __("id"),
                        "icon"    => "fa-light fa-user",
                        "current" => true,
                        "fields"  => [
                            "username" => [
                                "type"        => "input",
                                "label"       => __("username"),
                                "placeholder" => "johndoe",
                                "value"       => $user->username ?? '',
                            ],
                            "email" => [
                                "type"        => "input",
                                "label"       => __("email"),
                                "placeholder" => __("example@mail.com"),
                                "value"       => $user->email ?? '',
                            ],
                            "password" => [
                                "type"        => "password",
                                "label"       => __("password"),
                                "placeholder" => "********",
                                "value"       => '',
                            ],
                        ],
                    ],
                    "permissions_shop_organisation" => [
                        "label"   => __("Ecommerce Permissions"),
                        "title"   => __("Permissions"),
                        "icon"    => "fa-light fa-user-lock",
                        "current" => false,
                        "fields"  => [
                            "permissions" => [
                                "full"              => true,
                                "noSaveButton"      => true,
                                "type"              => "permissions",
                                "review"            => $reviewData,
                                'organisation_list' => $organisationList,
                                'updateRoute'       => [
                                    'method'     => 'patch',
                                    "name"       => "grp.models.user.pseudo-job-positions.update",
                                    'parameters' => [
                                        'user' => $user->id
                                        ]
                                    ],

                                    // 'list_authorised'           => Organisation::get()->flatMap(function (Organisation $organisation) {
                                    //     return [
                                    //         $organisation->slug         => [
                                    //             'authorised_shops'       =>
                                    //             $organisation->shops()->where('state', '!=', ShopStateEnum::CLOSED)->count(),
                                    //             'authorised_fulfilments' =>
                                    //             $organisation->shops()->where('type', ShopTypeEnum::FULFILMENT)->whereIn('state', [ShopStateEnum::IN_PROCESS, ShopStateEnum::OPEN, ShopStateEnum::CLOSING_DOWN])->count(),
                                    //             'authorised_warehouses' =>
                                    //             $organisation->warehouses()->where('state', '!=', WarehouseStateEnum::CLOSED)->count(),
                                    //             'authorised_productions' =>
                                    //             $organisation->productions()->where('state', '!=', ProductionStateEnum::CLOSED)->count(),
                                    //         ]
                                    //     ];
                                    // })->toArray(),

                                    // "label"             => __("permissions"),
                                'options'           => Organisation::get()->flatMap(function (Organisation $organisation) {
                                    return [
                                        $organisation->slug         => [
                                            'positions'       => JobPositionResource::collection($organisation->jobPositions),
                                            'shops'           => ShopResource::collection($organisation->shops()->where('type', '!=', ShopTypeEnum::FULFILMENT)->get()),
                                            'fulfilments'     => ShopResource::collection($organisation->shops()->where('type', '=', ShopTypeEnum::FULFILMENT)->get()),
                                            'warehouses'      => WarehouseResource::collection($organisation->warehouses),
                                        ]
                                    ];
                                })->toArray(),
                                // "value"             => $permissions,
                                "value"             => $user->pseudoJobPositions->flatMap(function (JobPosition $jobPosition) {
                                    return [
                                        $jobPosition->organisation->slug => [
                                            $jobPosition->code => match (array_key_first($jobPosition->pivot->scopes)) {
                                                class_basename(Shop::class) => [
                                                    'shops' => $jobPosition->organisation->shops->whereIn('id', Arr::get($jobPosition->pivot->scopes, class_basename(Shop::class)))->pluck('slug')
                                                ],
                                                default => null
                                            }
                                        ]
                                    ];
                                }),
                                "fullComponentArea" => true,
                            ],
                        ],
                    ],
                    // "permissions_agents" => [
                    //     "label"   => __("Agents Permissions"),
                    //     "title"   => __("Permissions"),
                    //     "icon"    => "fa-light fa-user-lock",
                    //     "current" => false,
                    //     "fields"  => [
                    //         "permissions" => [
                    //             "full"              => true,
                    //             "type"              => "permissions",
                    //             "label"             => __("permissions"),
                    //             "value"             => $permissions,
                    //             "fullComponentArea" => true,
                    //         ],
                    //     ],
                    // ],


                    // "permissions_digital_agency" => [
                    //     "label"   => __("Digital agency permissions"),
                    //     "title"   => __("Permissions"),
                    //     "icon"    => "fa-light fa-user-lock",
                    //     "current" => false,
                    //     "fields"  => [
                    //         "permissions" => [
                    //             "full"              => true,
                    //             "type"              => "permissions",
                    //             "label"             => __("permissions"),
                    //             "value"             => $permissions,
                    //             "fullComponentArea" => true,
                    //         ],
                    //     ],
                    // ],

                ],
                "args" => [
                    "updateRoute" => [
                        "name"       => "grp.models.user.update",
                        "parameters" => [$user->id],
                    ],
                ],
            ],
        ]);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowUser::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', "show", $routeName),
            routeParameters: $routeParameters,
            suffix: "(" . __("editing") . ")"
        );
    }
}
