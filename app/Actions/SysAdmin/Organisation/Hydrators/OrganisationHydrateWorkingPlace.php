<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Models\SysAdmin\Organisation;
use App\Models\HumanResources\Workplace;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateWorkingPlace implements ShouldBeUnique
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_workplace' => Workplace::count()
        ];

        $organisation->stats->update($stats);
    }
}
