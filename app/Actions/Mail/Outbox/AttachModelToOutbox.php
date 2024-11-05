<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Mail\Outbox;

use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\User;

class AttachModelToOutbox extends OrgAction
{
    public function handle(User|Customer|Prospect $model, Outbox $outbox): void
    {
        $model->subscribedOutboxes()->attach($outbox->id, [
            'group_id' => $outbox->group_id,
            'organisation_id' => $outbox->organisation_id,
            'shop_id' => $outbox->shop_id,
            'data' => '{}'
        ]);
    }

    public function action(User|Customer|Prospect $model, Outbox $outbox): void
    {
        $this->asAction       = true;
        $this->initialisationFromShop($outbox->shop, []);

        $this->handle($model, $outbox);
    }
}
