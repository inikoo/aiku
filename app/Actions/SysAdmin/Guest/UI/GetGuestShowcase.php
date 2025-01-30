<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\UI;

use App\Actions\Utils\GetLocationFromIp;
use App\Http\Resources\SysAdmin\UserShowcaseResource;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetGuestShowcase
{
    use AsObject;

    public function handle(Guest $guest)
    {
        // dd($guest);
        return [
            'data' => [
            'id'                      => $guest->id,
            'username'                => $guest->username,
            // 'avatar'                  => $guest->imageSources(48, 48),
            'email'                   => $guest->email,
            'about'                   => $guest->about,
            'contact_name'            => $guest->contact_name,
            // 'authorizedOrganisations' => $guest->authorisedOrganisations->map(fn ($organisation) => [
            //     'slug' => $organisation->slug,
            //     'name' => $organisation->name,
            //     'type' => $organisation->type,
            // ]),
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
