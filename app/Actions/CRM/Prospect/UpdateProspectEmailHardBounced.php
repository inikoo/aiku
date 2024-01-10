<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Nov 2023 22:42:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\CRM\Prospect;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProspectEmailHardBounced
{
    use AsAction;

    public function handle(Prospect $prospect, Carbon $date): void
    {
        $dataToUpdate = [];

        if (!$prospect->failed_at) {
            $dataToUpdate['failed_at'] = $date;
        }

        if ($prospect->state != ProspectStateEnum::SUCCESS) {
            $dataToUpdate['state']           = ProspectStateEnum::FAIL;
            $dataToUpdate['fail_status']     = ProspectFailStatusEnum::INVALID;
            $dataToUpdate['contacted_state'] = ProspectFailStatusEnum::NA;
        }

        UpdateProspect::run(
            $prospect,
            $dataToUpdate
        );
    }
}
