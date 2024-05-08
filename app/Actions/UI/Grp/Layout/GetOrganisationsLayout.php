<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrganisationsLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $navigation = [];
        foreach ($user->authorisedOrganisations as $organisation) {
            $navigation[$organisation->slug] = GetOrganisationNavigation::run($user, $organisation);
        }

        return $navigation;
    }
}
