<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Nov 2023 21:30:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\OrgAction;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\ActionRequest;

class UpdateProspectEmailUnsubscribed extends OrgAction
{
    public function handle(Prospect $prospect, Carbon $date): Prospect
    {
        $dataToUpdate = [
            'dont_contact_me' => true
        ];
        if (!$prospect->dont_contact_me_at) {
            $dataToUpdate['dont_contact_me_at'] = $date;
        }
        if (!$prospect->failed_at) {
            $dataToUpdate['failed_at'] = $date;
        }

        if ($prospect->state != ProspectStateEnum::SUCCESS) {
            $dataToUpdate['state']           = ProspectStateEnum::FAIL;
            $dataToUpdate['fail_status']     = ProspectFailStatusEnum::UNSUBSCRIBED;
            $dataToUpdate['contacted_state'] = ProspectContactedStateEnum::NA;
        }

        $prospect = UpdateProspect::run(
            $prospect,
            $dataToUpdate
        );

        return $prospect;
    }

    public function inShop(Shop $shop, Prospect $prospect, ActionRequest $request): void
    {
        $this->initialisationFromShop($shop, $request);
        $this->handle($prospect, now());
    }

    public function action(Prospect $prospect, Carbon $date): Prospect
    {
        $this->initialisation($prospect->organisation, []);

        return $this->handle($prospect, $date);
    }
}
