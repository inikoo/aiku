<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;
use Str;

class FetchAuroraEmailTrackingEvent extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!$this->auroraModelData->{'Email Tracking Event Type'}) {
            return;
        }


        //enum('Sent','Rejected by SES','Send','Read','Hard Bounce','Soft Bounce','Spam','Delivered','Opened','Clicked','Send to SES Error')
        $type = match ($this->auroraModelData->{'Email Tracking Event Type'}) {
            'Send to SES Error' => 'declined-by-provider',
            'Spam'              => 'marked-as-spam',
            default             => Str::kebab($this->auroraModelData->{'Email Tracking Event Type'})
        };

        $this->parsedData['dispatchedEmail'] = $this->parseDispatchedEmail($this->auroraModelData->{'Email Tracking Event Tracking Key'});

        if (!$this->parsedData['dispatchedEmail']) {
            return;
        }

        $data = [];


        if ($this->auroraModelData->{'Email Tracking Event Data'} and
            !(
                $this->auroraModelData->{'Email Tracking Event Data'}!='""'  or
                $this->auroraModelData->{'Email Tracking Event Data'}!='{}'
            )
        ) {
            $data['sns'] = json_decode($this->auroraModelData->{'Email Tracking Event Data'});
        }
        if ($this->auroraModelData->{'Email Tracking Event Note'} != '') {
            $data['source_original_note'] = $this->auroraModelData->{'Email Tracking Event Note'};
        }



        if ($this->auroraModelData->{'Email Tracking Event Status Code'} != '') {
            $data['status_code'] = $this->auroraModelData->{'Email Tracking Event Status Code'};
        }


        $this->parsedData['emailTrackingEvent'] = [
            'type'            => $type,
            'notification_id' => $this->auroraModelData->{'Email Tracking Event Message ID'},
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Email Tracking Event Key'},
            'created_at'      => $this->parseDate($this->auroraModelData->{'Email Tracking Event Date'}),
            'data'            => $data,
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Tracking Event Dimension')
            ->where('Email Tracking Event Key', $id)->first();
    }
}
