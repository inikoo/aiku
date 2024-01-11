<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Nov 2023 22:55:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\CRM\Prospect;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProspectEmailSoftBounced
{
    use AsAction;

    public function handle(Prospect $prospect, Carbon $date): void
    {
        $dataToUpdate = [
            'last_soft_bounced_at'=> $date
        ];


        if ($prospect->state == ProspectStateEnum::NO_CONTACTED or $prospect->state == ProspectStateEnum::CONTACTED) {

            $dataToUpdate['state']       = ProspectStateEnum::CONTACTED;

            if ($prospect->contacted_state== ProspectContactedStateEnum::NA) {
                $dataToUpdate['contacted_state'] = ProspectContactedStateEnum::SOFT_BOUNCED;
            }

        }

        UpdateProspect::run(
            $prospect,
            $dataToUpdate
        );
    }
}
