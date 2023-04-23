<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use Illuminate\Support\Facades\DB;
use Str;

class FetchAuroraDispatchedEmail extends FetchAurora
{
    protected function parseModel(): void
    {
        //enum('Ready','Sent to SES','Rejected by SES','Sent','Soft Bounce','Hard Bounce','Delivered','Spam','Opened','Clicked','Error')
        $state = match ($this->auroraModelData->{'Email Tracking State'}) {
            'Sent to SES'     => DispatchedEmailStateEnum::SENT_TO_PROVIDER,
            'Rejected by SES' => DispatchedEmailStateEnum::REJECTED_BY_PROVIDER,
            'Spam'            => DispatchedEmailStateEnum::MARKED_AS_SPAM,
            default           => Str::kebab($this->auroraModelData->{'Email Tracking State'})
        };

        $parent = null;
        if ($this->parseMailshot($this->auroraModelData->{'Email Tracking Email Mailshot Key'})) {
            $parent = $this->parseMailshot($this->auroraModelData->{'Email Tracking Email Mailshot Key'});
            if (!$parent) {
                print('Error Mailshot not found');
            }
        }
        if (!$parent) {
            $parent = $this->parseOutbox($this->auroraModelData->{'Email Tracking Email Template Type Key'});
        }

        $recipient = match ($this->auroraModelData->{'Email Tracking Recipient'}) {
            'Customer' => $this->parseCustomer($this->auroraModelData->{'Email Tracking Key'}),
            'Prospect' => $this->parseProspect($this->auroraModelData->{'Email Tracking Key'}),
            default    => null
        };

        $this->parsedData['recipient'] = $recipient;
        $this->parsedData['parent']    = $parent;
        $this->parsedData['email']     = $this->auroraModelData->{'Email Tracking Email'};


        $this->parsedData['dispatchedEmail'] = [
            'state'      => $state,
            'source_id'  => $this->auroraModelData->{'Email Tracking Key'},
            'created_at' => $this->parseDate($this->auroraModelData->{'Email Tracking Created Date'})
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Tracking Dimension')
            ->where('Email Tracking Key', $id)->first();
    }
}
