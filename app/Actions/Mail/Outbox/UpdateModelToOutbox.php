<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Mail\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\User;

class UpdateModelToOutbox extends OrgAction
{
    public function handle(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {
        $model->subscribedOutboxes()->updateExistingPivot($outbox->id, $modelData);

        GroupHydrateOutboxes::dispatch($outbox->group);
        OrganisationHydrateOutboxes::dispatch($outbox->organisation);
        ShopHydrateOutboxes::dispatch($outbox->shop);
    }
    public function rules(): array
    {
        return [
            'data' => ['somtimes'],
            'unsubscribed_at' => ['somtimes', 'date'],
        ];
    }

    public function action(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {
        $this->asAction       = true;
        $this->initialisationFromShop($outbox->shop, []);

        $this->handle($model, $outbox, $modelData);
    }
}