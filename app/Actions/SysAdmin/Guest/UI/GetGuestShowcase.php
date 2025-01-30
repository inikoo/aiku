<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\UI;

use App\Actions\SysAdmin\User\GetUserGroupScopeJobPositionsData;
use App\Actions\SysAdmin\User\GetUserOrganisationScopeJobPositionsData;
use App\Actions\Utils\GetLocationFromIp;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsObject;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\Http\Resources\Api\Dropshipping\ShopResource;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\SysAdmin\Organisation;

class GetGuestShowcase
{
    use AsObject;


    public function handle(Guest $guest)
    {
        // dd($guest);
        $user = $guest->getUser();

        $jobPositionsOrganisationsData = [];
        foreach ($guest->group->organisations as $organisation) {
            $jobPositionsOrganisationData                       = GetUserOrganisationScopeJobPositionsData::run($user, $organisation);
            $jobPositionsOrganisationsData[$organisation->slug] = $jobPositionsOrganisationData;
        }



        $permissionsGroupData = GetUserGroupScopeJobPositionsData::run($user);

        return [
            'data' => [
            'id'                      => $guest->id,
            'username'                => $guest->username,
            // 'avatar'                  => $guest->imageSources(48, 48),
            'email'                   => $guest->email,
            'about'                   => $guest->about,
            'contact_name'            => $guest->contact_name,
            // 'authorizedOrganisations' => $user->authorisedOrganisations->map(function ($organisation) {
            //     return [
            //         'slug' => $organisation->slug,
            //         'name' => $organisation->name,
            //         'type' => $organisation->type,
            //     ];
            // }),
            'permissions_pictogram' => [
                'organisation_list' => OrganisationsResource::collection($user->group->organisations),
                "current_organisation"  => $user->getOrganisation(),
                'options'           => Organisation::get()->flatMap(function (Organisation $organisation) {
                    return [
                        $organisation->slug => [
                            'positions'   => JobPositionResource::collection($organisation->jobPositions),
                            'shops'       => \App\Http\Resources\Catalogue\ShopResource::collection($organisation->shops()->where('type', '!=', ShopTypeEnum::FULFILMENT)->get()),
                            'fulfilments' => ShopResource::collection($organisation->shops()->where('type', '=', ShopTypeEnum::FULFILMENT)->get()),
                            'warehouses'  => WarehouseResource::collection($organisation->warehouses),
                        ]
                    ];
                })->toArray(),
                'group' => $permissionsGroupData,
                'organisations' => $jobPositionsOrganisationsData
            ],
            // 'permissions'             => $guest->getAllPermissions()->pluck('name')->toArray(),
            'last_active_at'          => $guest->stats->last_active_at,
            'last_login'              => [
                'ip'          => $guest->stats->last_login_ip,
                'geolocation' => GetLocationFromIp::run($guest->stats->last_login_ip)
            ]
        ]
            ];
    }
}
