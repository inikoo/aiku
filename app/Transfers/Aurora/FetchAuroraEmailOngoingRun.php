<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 08:14:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Enums\Comms\EmailOngoingRun\EmailOngoingRunStatusEnum;
use App\Enums\Comms\EmailOngoingRun\EmailOngoingRunCodeEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraEmailOngoingRun extends FetchAurora
{
    protected function parseModel(): void
    {
        //enum(','New Customer','Delivery Note Dispatched','Delivery Note Undispatched','Invoice Deleted','New Order','AbandonedCart','Delivery Confirmation','GR Reminder','Invite','Invite Mailshot','Invite Full Mailshot','Marketing','Newsletter','OOS Notification','Order Confirmation','Password Reminder','Registration','Registration Approved','Registration Rejected')

        if (!in_array(
            $this->auroraModelData->{'Email Campaign Type Code'},
            [
                'Registration',
                'Registration Rejected',
                'Registration Approved',
                'Password Reminder',
                'Order Confirmation',
                'Delivery Confirmation',
                'OOS Notification',
                'Basket Low Stock',
                'Basket Reminder 1',
                'Basket Reminder 2',
                'Basket Reminder 3',
                'AbandonedCart',
                'GR Reminder',
            ]
        )) {
            return;
        }

        if (!$this->auroraModelData->{'Email Template Key'}) {
            return;
        }


        $shop = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Type Store Key'});

        $status = match ($this->auroraModelData->{'Email Campaign Type Status'}) {
            'Active' => EmailOngoingRunStatusEnum::ACTIVE,
            'Suspended' => EmailOngoingRunStatusEnum::SUSPENDED,
            'InProcess' => EmailOngoingRunStatusEnum::IN_PROCESS,
        };


        $code = match ($this->auroraModelData->{'Email Campaign Type Code'}) {
            'Registration' => EmailOngoingRunCodeEnum::REGISTRATION,
            'Registration Rejected' => EmailOngoingRunCodeEnum::REGISTRATION_REJECTED,
            'Registration Approved' => EmailOngoingRunCodeEnum::REGISTRATION_APPROVED,
            'Password Reminder' => EmailOngoingRunCodeEnum::PASSWORD_REMINDER,
            'Order Confirmation' => EmailOngoingRunCodeEnum::ORDER_CONFIRMATION,
            'Delivery Confirmation' => EmailOngoingRunCodeEnum::DELIVERY_CONFIRMATION,

            'Basket Low Stock' => EmailOngoingRunCodeEnum::BASKET_LOW_STOCK,
            'Basket Reminder 1' => EmailOngoingRunCodeEnum::BASKET_REMINDER_1,
            'Basket Reminder 2' => EmailOngoingRunCodeEnum::BASKET_REMINDER_2,
            'Basket Reminder 3' => EmailOngoingRunCodeEnum::BASKET_REMINDER_3,
            'AbandonedCart' => EmailOngoingRunCodeEnum::ABANDONED_CART,
            'GR Reminder' => EmailOngoingRunCodeEnum::REORDER_REMINDER,
            'OOS Notification' => EmailOngoingRunCodeEnum::OOS_NOTIFICATION,

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
            'code'            => $code,
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
