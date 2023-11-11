<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Jul 2023 17:06:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Auth\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Guest $guest): void
    {

        $guest->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'sysadmin',
                'title'       => trim($guest->slug.' '.$guest->contact_name),
                'description' => $guest->contact_name.' '.$guest->email.' '.$guest->phone.' '.$guest->company_name
            ]
        );
    }


}
