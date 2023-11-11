<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation\Hydrators;

use App\Models\HumanResources\Workplace;
use App\Models\Organisation\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateWorkingPlace implements ShouldBeUnique
{
    use AsAction;
    use HasOrganisationHydrate;

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_workplace' => Workplace::count()
        ];

        $organisation->stats->update($stats);
    }
}
