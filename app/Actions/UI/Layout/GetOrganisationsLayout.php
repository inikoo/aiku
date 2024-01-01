<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 22:08:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrganisationsLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $navigation=[];
        foreach($user->authorisedOrganisations as $organisation) {
            $navigation[$organisation->slug]=GetOrganisationNavigation::run($user, $organisation);
        }
        return $navigation;


    }
}
