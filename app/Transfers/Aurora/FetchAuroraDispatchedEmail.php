<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\DispatchedEmail\DispatchedEmailProviderEnum;
use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\DB;
use Str;

class FetchAuroraDispatchedEmail extends FetchAurora
{
    protected function parseModel(): void
    {

        if (!$this->auroraModelData->{'Email Tracking Recipient Key'}) {
            return;
        }

        if (!$this->auroraModelData->{'Email Tracking Email'}) {
            return;
        }


        //enum('Ready','Sent to SES','Rejected by SES','Sent','Soft Bounce','Hard Bounce','Delivered','Spam','Opened','Clicked','Error')
        $state = match ($this->auroraModelData->{'Email Tracking State'}) {
            'Sent to SES' => DispatchedEmailStateEnum::SENT_TO_PROVIDER,
            'Rejected by SES' => DispatchedEmailStateEnum::REJECTED_BY_PROVIDER,
            'Spam' => DispatchedEmailStateEnum::SPAM,
            default => Str::kebab($this->auroraModelData->{'Email Tracking State'})
        };

        $parent = null;
        if ($this->auroraModelData->{'Email Tracking Email Mailshot Key'}) {

            $parent = $this->parseMailshot($this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Email Mailshot Key'});

            if (!$parent) {
                $parent = $this->parseEmailBulkRun($this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Email Mailshot Key'});
            }

        }


        if (!$parent) {

            $outbox = Outbox::withTrashed()
                ->whereJsonContains('sources->outboxes', $this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Email Template Type Key'})
                ->first();


            dd($outbox->emailOngoingRun);
            if ($outbox->type == OutboxTypeEnum::USER_NOTIFICATION) {
                $parent = $outbox->emailOngoingRun;

            }

        }



        if (!$parent) {
            dd($this->auroraModelData);
        }

        $recipient = match ($this->auroraModelData->{'Email Tracking Recipient'}) {
            'Customer' => $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Recipient Key'}),
            'Prospect' => $this->parseProspect($this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Recipient Key'}),
            'User' => $this->parseUser($this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Recipient Key'}),

            default => null
        };

        if (!$recipient) {
            return;
            // print_r($this->auroraModelData);
        }

        $this->parsedData['recipient'] = $recipient;
        $this->parsedData['parent']    = $parent;


        $this->parsedData['dispatchedEmail'] = [
            'provider'             => DispatchedEmailProviderEnum::SES,
            'provider_dispatch_id' => $this->auroraModelData->{'Email Tracking SES Id'},
            'email_address'        => $this->auroraModelData->{'Email Tracking Email'},
            'state'                => $state,
            'fetched_at'           => now(),
            'last_fetched_at'      => now(),
            'source_id'            => $this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Key'},
            'created_at'           => $this->parseDatetime($this->auroraModelData->{'Email Tracking Created Date'})
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Tracking Dimension')
            ->where('Email Tracking Key', $id)->first();
    }
}
