<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 08:14:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Enums\Comms\EmailOngoingRun\EmailOngoingRunStatusEnum;
use App\Enums\Comms\EmailOngoingRun\EmailOngoingRunTypeEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraEmailOngoingRun extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!in_array(
            $this->auroraModelData->{'Email Campaign Type Code'},
            [
                'Registration',
                'Registration Rejected',
                'Registration Approved',
                'Password Reminder',
                'Order Confirmation',
                'Delivery Confirmation'
            ]
        )) {
            return;
        }

        if (!$this->auroraModelData->{'Email Template Key'}) {
            return;
        }


        $shop = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Type Store Key'});

        //enum('Active','Suspended','InProcess')
        $status = match ($this->auroraModelData->{'Email Campaign Type Status'}) {
            'Active' => EmailOngoingRunStatusEnum::ACTIVE,
            'Suspended' => EmailOngoingRunStatusEnum::SUSPENDED,
            'InProcess' => EmailOngoingRunStatusEnum::IN_PROCESS,
        };


        $type                     = match ($this->auroraModelData->{'Email Campaign Type Code'}) {
            'Registration' => EmailOngoingRunTypeEnum::REGISTRATION,
            'Registration Rejected' => EmailOngoingRunTypeEnum::REGISTRATION_REJECTED,
            'Registration Approved' => EmailOngoingRunTypeEnum::REGISTRATION_APPROVED,
            'Password Reminder' => EmailOngoingRunTypeEnum::PASSWORD_REMINDER,
            'Order Confirmation' => EmailOngoingRunTypeEnum::ORDER_CONFIRMATION,
            'Delivery Confirmation' => EmailOngoingRunTypeEnum::DELIVERY_CONFIRMATION,
        };
        $this->parsedData['shop'] = $shop;


        $snapshotPublishedAt = $this->parseDatetime($this->auroraModelData->{'Email Template Last Edited'});

        if (!$snapshotPublishedAt) {
            $snapshotPublishedAt = $this->parseDatetime($this->auroraModelData->{'Email Template Created'});
        }
        if (!$snapshotPublishedAt) {
            $snapshotPublishedAt = now();
        }

        $createdAt = $this->parseDatetime($this->auroraModelData->{'Email Template Created'});
        if (!$createdAt) {
            $createdAt = $this->parseDatetime($this->auroraModelData->{'Email Template Last Edited'});
        }

        if ($status == EmailOngoingRunStatusEnum::IN_PROCESS) {
            $snapshotPublishedAt = null;
        }

        $this->parsedData['email_ongoing_run'] = [
            'type'            => $type,
            'status'          => $status,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Type Key'},
            'created_at'      => $createdAt,
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
        ];


        $snapShotState = match ($status) {
            EmailOngoingRunStatusEnum::IN_PROCESS => SnapshotStateEnum::UNPUBLISHED,
            default => SnapshotStateEnum::LIVE,
        };

        $this->parsedData['snapshot'] = [
            'subject'         => $this->auroraModelData->{'Email Template Subject'},
            'builder'         => EmailBuilderEnum::BEEFREE,
            'layout'          => json_decode($this->auroraModelData->{'Email Template Editing JSON'}, true),
            'compiled_layout' => $this->auroraModelData->{'Email Template HTML'},
            'state'           => $snapShotState,
            'published_at'    => $snapshotPublishedAt,
            'recyclable'      => false,
            'first_commit'    => true,
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Email Template Key'},

            'snapshot_state'        => $snapShotState,
            'snapshot_published_at' => $snapshotPublishedAt,
            'snapshot_recyclable'   => false,
            'snapshot_first_commit' => true,
            'snapshot_source_id'    => $this->organisation->id.':'.$this->auroraModelData->{'Email Template Key'},

        ];
        //dd($this->auroraModelData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Campaign Type Dimension')
            ->leftJoin('Email Template Dimension', 'Email Template Email Campaign Type Key', 'Email Campaign Type Key')
            ->where('Email Campaign Type Key', $id)->first();
    }
}
