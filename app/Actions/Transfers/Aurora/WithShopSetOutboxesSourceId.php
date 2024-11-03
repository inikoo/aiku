<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 03 Nov 2024 21:54:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Facades\DB;

trait WithShopSetOutboxesSourceId
{
    public function setShopSetOutboxesSourceId(Shop $shop): void
    {
        $shopSourceId = explode(':', $shop->source_id);

        $auroraOutboxes = DB::connection('aurora')->table('Email Campaign Type Dimension')
            ->where('Email Campaign Type Store Key', $shopSourceId[1])
            ->get()
            ->pluck('Email Campaign Type Key', 'Email Campaign Type Code')->all();


        foreach ($shop->outboxes as $outbox) {
            $sourceId = match ($outbox->type) {
                OutboxTypeEnum::NEW_CUSTOMER => $auroraOutboxes['New Customer'],
                OutboxTypeEnum::ABANDONED_CART => $auroraOutboxes['AbandonedCart'],
                OutboxTypeEnum::BASKET_LOW_STOCK => $auroraOutboxes['Basket Low Stock'] ?? null,
                OutboxTypeEnum::BASKET_REMINDER_1 => $auroraOutboxes['Basket Reminder 1'],
                OutboxTypeEnum::BASKET_REMINDER_2 => $auroraOutboxes['Basket Reminder 2'],
                OutboxTypeEnum::BASKET_REMINDER_3 => $auroraOutboxes['Basket Reminder 3'],
                OutboxTypeEnum::DELIVERY_CONFIRMATION => $auroraOutboxes['Delivery Confirmation'],
                OutboxTypeEnum::DELIVERY_NOTE_DISPATCHED => $auroraOutboxes['Delivery Note Dispatched'],
                OutboxTypeEnum::DELIVERY_NOTE_UNDISPATCHED => $auroraOutboxes['Delivery Note Undispatched'],
                //    'invite' => $auroraOutboxes['Invite'],
                //    'invite-full-mailshot' => $auroraOutboxes['Invite Full Mailshot'],
                //   'invite-mailshot' => $auroraOutboxes['Invite Mailshot'],
                //   'invoice-deleted' => $auroraOutboxes['Invoice Deleted'],
                OutboxTypeEnum::MARKETING => $auroraOutboxes['Marketing'],
                OutboxTypeEnum::NEW_ORDER => $auroraOutboxes['New Order'],
                OutboxTypeEnum::NEWSLETTER => $auroraOutboxes['Newsletter'],
                OutboxTypeEnum::OOS_NOTIFICATION => $auroraOutboxes['OOS Notification'],
                OutboxTypeEnum::ORDER_CONFIRMATION => $auroraOutboxes['Order Confirmation'],
                OutboxTypeEnum::PASSWORD_REMINDER => $auroraOutboxes['Password Reminder'],
                OutboxTypeEnum::REGISTRATION => $auroraOutboxes['Registration'],
                OutboxTypeEnum::REGISTRATION_APPROVED => $auroraOutboxes['Registration Approved'],
                OutboxTypeEnum::REGISTRATION_REJECTED => $auroraOutboxes['Registration Rejected'],
                OutboxTypeEnum::REORDER_REMINDER => $auroraOutboxes['GR Reminder'],


                default => null
            };



            if ($sourceId) {
                $outbox->update(
                    [
                        'source_id' => $shop->organisation->id.':'.$sourceId
                    ]
                );
            }



        }


    }
}
