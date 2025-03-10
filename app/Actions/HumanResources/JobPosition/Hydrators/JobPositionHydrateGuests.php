<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 May 2024 10:58:10 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\Hydrators;

use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateJobPositionsShare;
use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\JobPosition;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class JobPositionHydrateGuests
{
    use AsAction;
    use WithNormalise;

    private JobPosition $jobPosition;

    public function __construct(JobPosition $jobPosition)
    {
        $this->jobPosition = $jobPosition;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->jobPosition->id))->dontRelease()];
    }

    public function handle(JobPosition $jobPosition): void
    {

        //todo

        //        $numberGuests        = $jobPosition->guests()->count();
        //        $numberGuestsWorkTime = $jobPosition->employees()->sum('share');
        //
        //        $jobPosition->stats()->update(
        //            [
        //                'number_guests'           => $numberGuests,
        //                'number_guests_work_time' => $numberGuestsWorkTime
        //            ]
        //        );
        //
        //        if ($jobPosition->organisation_id) {
        //            OrganisationHydrateJobPositionsShare::run($jobPosition->organisation);
        //        }


    }




}
