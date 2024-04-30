<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\SysAdmin\Guest;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateClockings
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
            'number_clockings' => $guest->clockings()->count(),
        ];

        $guest->stats()->update($stats);
    }


}
