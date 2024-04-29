<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 19:27:30 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\SysAdmin\Guest;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateTimesheets
{
    use AsAction;
    use WithEnumStats;

    private Guest $guest;

    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->guest->id))->dontRelease()];
    }

    public function handle(Guest $guest): void
    {
        $stats = [
            'number_timesheets' => $guest->timesheets()->count(),
        ];



        $guest->stats()->update($stats);
    }


}
