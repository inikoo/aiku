<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateClockings;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateTimesheets;
use App\Models\SysAdmin\Guest;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateGuest
{
    use asAction;

    public string $commandSignature = 'guest:hydrate';


    public function handle(Guest $guest): void
    {

        GuestHydrateTimesheets::run($guest);
        GuestHydrateClockings::run($guest);
    }


    public function asCommand(Command $command): int
    {

        $command->withProgressBar(Guest::all(), function (Guest $guest) {
            $this->handle($guest);
        });

        return 0;
    }
}
