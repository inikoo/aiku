<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Organisation\Hydrators;

use App\Models\Auth\Guest;
use App\Models\Grouping\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateGuests implements ShouldBeUnique
{
    use AsAction;
    use HasOrganisationHydrate;

    public function handle(Organisation $organisation): void
    {
        $numberGuests = Guest::count();

        $numberActiveGuests = Guest::where('status', true)
            ->count();


        $stats = [
            'number_guests'                 => $numberGuests,
            'number_guests_status_active'   => $numberActiveGuests,
            'number_guests_status_inactive' => $numberGuests - $numberActiveGuests,
        ];


        $organisation->stats->update($stats);
    }
}
