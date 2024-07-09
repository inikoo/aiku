<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\Hydrators;

use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(Guest $guest): void
    {

        $guest->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'    => $guest->group_id,
                'section'     => 'sysadmin',
                'title'       => trim($guest->slug.' '.$guest->contact_name),
                'description' => $guest->contact_name.' '.$guest->email.' '.$guest->phone.' '.$guest->company_name
            ]
        );
    }


}
