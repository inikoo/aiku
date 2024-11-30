<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrganisationsLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $navigation = [];

        foreach ($user->authorisedOrganisations as $organisation) {
            if ($organisation->type == OrganisationTypeEnum::AGENT) {
                $navigation[$organisation->slug] = GetAgentOrganisationNavigation::run($user, $organisation);
            } elseif ($organisation->type == OrganisationTypeEnum::SHOP) {
                $navigation[$organisation->slug] = GetOrganisationNavigation::run($user, $organisation);
            } elseif ($organisation->type == OrganisationTypeEnum::DIGITAL_AGENCY) {
                $navigation[$organisation->slug] = GetDigitalAgencyOrganisationNavigation::run($user, $organisation);
            }
        }

        return $navigation;
    }
}
