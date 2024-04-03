<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Events\BroadcastFulfilmentCustomerNotification;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SendPalletDeliveryNotification extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletDelivery->refresh();

        broadcast(new BroadcastFulfilmentCustomerNotification(
            $palletDelivery->group,
            $palletDelivery
        ))->toOthers();
    }
}
