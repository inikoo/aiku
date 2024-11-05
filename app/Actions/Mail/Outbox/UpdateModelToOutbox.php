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

class UpdateModelToOutbox extends OrgAction
{
    public function handle(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {
        $model->subscribedOutboxes()->updateExistingPivot($outbox->id, $modelData);
    }
    public function rules(): array
    {
        return [
            'data' => ['somtimes', 'required'],
            'unsubscribed_at' => ['somtimes', 'required', 'date'],
        ];
    }

    public function action(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {
        $this->asAction       = true;
        $this->initialisationFromShop($outbox->shop, []);

        $this->handle($model, $outbox, $modelData);
    }
}
