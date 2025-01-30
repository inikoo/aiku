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
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\Http\Resources\SysAdmin\UserShowcaseResource;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsObject;

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



        $organisations = $user->group->organisations;
        $orgIds = $user->getOrganisations()->pluck('id')->toArray();

        $reviewData    = $organisations->mapWithKeys(function ($organisation) use ($user, $orgIds) {
            return [
                $organisation->slug => [
                    'is_employee' => in_array($organisation->id, $orgIds),
                    'number_job_positions' => $organisation->humanResourcesStats->number_job_positions,
                    'job_positions'        => $organisation->jobPositions->mapWithKeys(function ($jobPosition) {
                        return [
                            $jobPosition->slug => [
                                'job_position' => $jobPosition->name,
                                'number_roles' => $jobPosition->stats->number_roles
                            ]
                        ];
                    })
                ]
            ];
        })->toArray();

        return [
            'data' => [
            'id'                      => $guest->id,
            'username'                => $guest->username,
            // 'avatar'                  => $guest->imageSources(48, 48),
            'email'                   => $guest->email,
            'about'                   => $guest->about,
            'contact_name'            => $guest->contact_name,

            'authorizedOrganisations' => $jobPositionsOrganisationsData,
            'reviewData'              => $reviewData,
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
