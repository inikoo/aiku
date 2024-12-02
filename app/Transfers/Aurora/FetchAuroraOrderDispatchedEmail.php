<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 14 Nov 2024 14:27:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\Outbox\OutboxCodeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrderDispatchedEmail extends FetchAurora
{
    protected function parseModel(): void
    {
        $order = $this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Order Sent Email Order Key'});
        if (!$order) {
            return;
        }
        $dispatchedEmail = $this->parseDispatchedEmail($this->organisation->id.':'.$this->auroraModelData->{'Order Sent Email Email Tracking Key'});
        if (!$dispatchedEmail) {
            return;
        }

        $outboxType = match ($this->auroraModelData->{'Order Sent Email Type'}) {
            'Order Notification' => OutboxCodeEnum::ORDER_CONFIRMATION,
            'Dispatch Notification' => OutboxCodeEnum::DELIVERY_CONFIRMATION,
            'Basket Reminder 1' => OutboxCodeEnum::BASKET_REMINDER_1,
            'Basket Reminder 2' => OutboxCodeEnum::BASKET_REMINDER_2,
            'Basket Reminder 3' => OutboxCodeEnum::BASKET_REMINDER_3,
            default => null
        };
        if ($outboxType == null) {
            return;
        }

        $outbox = $order->shop->outboxes()->where('type', $outboxType)->first();
        if (!$outbox) {
            return;
        }


        $this->parsedData['order']           = $order;
        $this->parsedData['dispatchedEmail'] = $dispatchedEmail;
        $this->parsedData['outbox']          = $outbox;

        $this->parsedData['modelHasDispatchedEmail'] = [
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Order Sent Email Bridge Key'},
            'fetched_at' => now(),
            'last_fetched_at' => now(),
        ];


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Sent Email Bridge')
            ->where('Order Sent Email Bridge Key', $id)->first();
    }
}
