<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 14:27:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProspectEmailUndoUnsubscribed
{
    use AsAction;

    public function handle(Prospect $prospect): void
    {
        $dataToUpdate = [
            'dont_contact_me'    => false,
            'dont_contact_me_at' => null,
            'failed_at'          => null,
        ];


        if ($prospect->state != ProspectStateEnum::SUCCESS) {
            $state          = ProspectStateEnum::CONTACTED;
            $contactedState = ProspectContactedStateEnum::NEVER_OPEN;

            if(!$prospect->last_contacted_at) {
                $state          = ProspectStateEnum::NO_CONTACTED;
                $contactedState = ProspectContactedStateEnum::NA;
            }

            if($prospect->last_opened_at) {
                $contactedState = ProspectContactedStateEnum::OPEN;
            }

            if($prospect->last_clicked_at) {
                $contactedState = ProspectContactedStateEnum::CLICKED;
            }




            $dataToUpdate['state']           = $state;
            $dataToUpdate['fail_status']     = ProspectFailStatusEnum::NA;
            $dataToUpdate['contacted_state'] = $contactedState;
        }

        UpdateProspect::run(
            $prospect,
            $dataToUpdate
        );
    }

    public function inShop(Shop $shop, Prospect $prospect): void
    {
        $this->handle($prospect);
    }
}
