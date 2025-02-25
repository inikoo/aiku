<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 14:27:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\OrgAction;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\ActionRequest;

class UpdateProspectEmailUndoUnsubscribed extends OrgAction
{
    public function handle(Prospect $prospect): Prospect
    {
        $dataToUpdate = [
            'dont_contact_me'    => false,
            'dont_contact_me_at' => null,
            'failed_at'          => null,
        ];


        if ($prospect->state != ProspectStateEnum::SUCCESS) {
            $state          = ProspectStateEnum::CONTACTED;
            $contactedState = ProspectContactedStateEnum::NEVER_OPEN;

            if (!$prospect->last_contacted_at) {
                $state          = ProspectStateEnum::NO_CONTACTED;
                $contactedState = ProspectContactedStateEnum::NA;
            }

            if ($prospect->last_opened_at) {
                $contactedState = ProspectContactedStateEnum::OPEN;
            }

            if ($prospect->last_clicked_at) {
                $contactedState = ProspectContactedStateEnum::CLICKED;
            }




            $dataToUpdate['state']           = $state;
            $dataToUpdate['fail_status']     = ProspectFailStatusEnum::NA;
            $dataToUpdate['contacted_state'] = $contactedState;
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
        $this->handle($prospect);
    }

    public function action(Prospect $prospect): Prospect
    {
        $this->initialisation($prospect->organisation, []);

        return $this->handle($prospect);
    }
}
