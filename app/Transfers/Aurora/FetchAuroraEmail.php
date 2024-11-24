<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\Email\EmailBuilderEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraEmail extends FetchAurora
{
    protected function parseModel(): void
    {
        $parent = null;

        $builder = EmailBuilderEnum::BEEFREE;

        $outbox = $this->parseOutbox($this->organisation->id.':'.$this->auroraModelData->{'Email Template Email Campaign Type Key'});


        if ($this->auroraModelData->{'Email Template Scope'} == 'EmailCampaign' or $this->auroraModelData->{'Email Template Scope'} == 'Mailshot') {
            $emailCampaignData = DB::connection('aurora')->table('Email Campaign Dimension')->where('Email Campaign Key', $this->auroraModelData->{'Email Template Scope Key'})->first();
            if (!$emailCampaignData) {
                return;
            }
            if (in_array($emailCampaignData->{'Email Campaign Type'}, ['Newsletter', 'Marketing', 'Invite Full Mailshot', 'AbandonedCart'])) {
                $mailshot = $this->parseMailshot($this->organisation->id.':'.$emailCampaignData->{'Email Campaign Key'});
                if (!$mailshot) {
                    dd($emailCampaignData);
                }

                $outbox = $mailshot->outbox;
                $parent = $mailshot;
            } else {
                $emailRun = $this->parseEmailRun($this->organisation->id.':'.$emailCampaignData->{'Email Campaign Key'});
                if (!$emailRun) {
                    dd($emailCampaignData);
                }

                $outbox = $emailRun->outbox;
                $parent = $emailRun;
            }
        } elseif ($this->auroraModelData->{'Email Template Scope'} == 'EmailCampaignType') {
            $emailCampaignTypeData = DB::connection('aurora')->table('Email Campaign Type Dimension')->where('Email Campaign Type Key', $this->auroraModelData->{'Email Template Scope Key'})->first();

            return;
        }


        if (!$outbox) {
            dd($this->auroraModelData);
        }


        $subject = trim($this->auroraModelData->{'Email Template Subject'});
        $subject = preg_replace('/\s+/', ' ', $subject);

        if ($subject == '') {
            $subject = '⚠️ No subject 😕';
        }

        if (!$parent) {
            dd($this->auroraModelData);
        }


        $this->parsedData['outbox'] = $outbox;
        $this->parsedData['parent'] = $parent;
        $this->parsedData['email']  = [

            'subject'         => $subject,
            'builder'         => $builder,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Email Template Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'layout'          => json_decode($this->auroraModelData->{'Email Template Editing JSON'}, true)

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Template Dimension')
            ->where('Email Template Key', $id)->first();
    }
}
