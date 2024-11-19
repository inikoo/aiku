<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateSubscriber;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Models\Comms\Outbox;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\SysAdmin\User;

class UpdateModelToOutbox extends OrgAction
{
    public function handle(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {

        $subscription = $model->subscribedOutboxes()
                        ->where('outbox_id', $outbox->id)
                        ->first();

        if ($subscription) {
            $subscription->update($modelData);

            OutboxHydrateSubscriber::dispatch($outbox);
            GroupHydrateOutboxes::dispatch($outbox->group);
            OrganisationHydrateOutboxes::dispatch($outbox->organisation);
            ShopHydrateOutboxes::dispatch($outbox->shop);
        }
    }
    public function rules(): array
    {
        return [
            'data' => ['sometimes'],
            'unsubscribed_at' => ['sometimes', 'date'],
        ];
    }

    public function action(User|Customer|Prospect $model, Outbox $outbox, array $modelData): void
    {
        $this->asAction       = true;
        $this->initialisationFromShop($outbox->shop, []);

        $this->handle($model, $outbox, $modelData);
    }
}
