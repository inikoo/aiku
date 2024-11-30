<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraMailshot extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!in_array($this->auroraModelData->{'Email Campaign Type'}, ['Newsletter', 'Marketing', 'Invite Full Mailshot', 'AbandonedCart'])) {
            return;
        }

        $shop = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Store Key'});



        //enum('InProcess','SetRecipients','ComposingEmail','Ready','Scheduled','Sending','Sent','Cancelled','Stopped')
        $state = match ($this->auroraModelData->{'Email Campaign State'}) {
            'InProcess', 'SetRecipients', 'ComposingEmail' => MailshotStateEnum::IN_PROCESS,
            'Ready' => MailshotStateEnum::READY,
            'Scheduled' => MailshotStateEnum::SCHEDULED,
            'Sending' => MailshotStateEnum::SENDING,
            'Sent' => MailshotStateEnum::SENT,
            'Cancelled' => MailshotStateEnum::CANCELLED,
            'Stopped' => MailshotStateEnum::STOPPED,
        };



        if ($this->auroraModelData->{'Email Campaign Type'} == 'Newsletter') {
            $type   = MailshotTypeEnum::NEWSLETTER;
            $outbox = $shop->outboxes()->where('code', OutboxCodeEnum::NEWSLETTER)->first();
        } elseif ($this->auroraModelData->{'Email Campaign Type'} == 'Marketing') {
            $type   = MailshotTypeEnum::MARKETING;
            $outbox = $shop->outboxes()->where('code', OutboxCodeEnum::MARKETING)->first();
        } elseif ($this->auroraModelData->{'Email Campaign Type'} == 'Invite Full Mailshot') {
            $type   = MailshotTypeEnum::INVITE;
            $outbox = $shop->outboxes()->where('code', OutboxCodeEnum::INVITE)->first();
        } elseif ($this->auroraModelData->{'Email Campaign Type'} == 'AbandonedCart') {
            $type   = MailshotTypeEnum::ABANDONED_CART;
            $outbox = $shop->outboxes()->where('code', OutboxCodeEnum::ABANDONED_CART)->first();
        } else {
            dd($this->auroraModelData->{'Email Campaign Type'});
        }


        if (!$outbox) {
            dd($this->auroraModelData);
        }


        $this->parsedData['outbox']   = $outbox;

        $this->parsedData['source_template_id'] = $this->auroraModelData->{'Email Campaign Email Template Key'};

        $this->parsedData['mailshot'] = [
            'subject'    => $this->auroraModelData->{'Email Campaign Name'},
            'type'       => $type,
            'state'      => $state,
            'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Key'},
            'created_at' => $this->parseDatetime($this->auroraModelData->{'Email Campaign Creation Date'}),

            'ready_at'         => $this->parseDatetime($this->auroraModelData->{'Email Campaign Composed Date'}),
            'scheduled_at'     => $this->parseDatetime($this->auroraModelData->{'Email Campaign Scheduled Date'}),
            'start_sending_at' => $this->parseDatetime($this->auroraModelData->{'Email Campaign Start Send Date'}),
            'sent_at'          => $this->parseDatetime($this->auroraModelData->{'Email Campaign End Send Date'}),
            'stopped_at'       => $this->parseDatetime($this->auroraModelData->{'Email Campaign Stopped Date'}),

            'recipients_recipe' => [],
            'fetched_at'        => now(),
            'last_fetched_at'   => now(),
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->where('Email Campaign Key', $id)->first();
    }
}
