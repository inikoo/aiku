<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateJobPositionsShare
{
    use AsAction;
    use WithNormalise;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {

        $share           = [];
        $shareWithGuests = [];
        /** @var JobPosition $jobPosition */
        foreach ($organisation->jobPositions as $jobPosition) {
            $share[$jobPosition->id]           = $jobPosition->number_employee_work_time;
            $shareWithGuests[$jobPosition->id] = $jobPosition->number_employee_work_time+$jobPosition->number_guest_work_time;

        }
        $employeeShares=$this->normalise(collect($share));
        foreach ($employeeShares as $id => $share) {
            JobPosition::find($id)->stats()->update(
                [
                    'share_work_time' => $share
                ]
            );
        }


        $employeeWithGuestsShares=$this->normalise(collect($shareWithGuests));
        foreach ($employeeWithGuestsShares as $id => $share) {
            JobPosition::find($id)->stats()->update(
                [
                    'share_work_time_including_guests' => $share
                ]
            );
        }

    }
}
