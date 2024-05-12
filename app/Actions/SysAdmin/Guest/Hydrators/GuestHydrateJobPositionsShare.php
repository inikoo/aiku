<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 May 2024 11:22:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\Hydrators;

use App\Actions\Traits\WithJobPositionableShare;
use App\Actions\Traits\WithNormalise;

use App\Models\SysAdmin\Guest;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateJobPositionsShare
{
    use AsAction;
    use WithNormalise;
    use WithJobPositionableShare;

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
        foreach ($this->getJobPositionShares($guest) as $job_position_id => $share) {
            $guest->jobPositions()->updateExistingPivot($job_position_id, [
                'share' => $share,
            ]);
        }
    }


}
