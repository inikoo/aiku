<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 03 Nov 2024 21:54:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait WithShopSetOutboxesSourceId
{
    public function setShopSetOutboxesSourceId(Shop $shop): void
    {
        $shopSourceId = explode(':', $shop->source_id);

        foreach (DB::connection('aurora')->table('Email Campaign Type Dimension')
            ->where('Email Campaign Type Store Key', $shopSourceId[1])
            ->get() as $auroraOutboxes) {

            $outboxType = $this->mapAuroraOutboxCode($auroraOutboxes->{'Email Campaign Type Code'});
            //print '>>'.$auroraOutboxes->{'Email Campaign Type Code'}."\n";
            //print_r($outboxType);
            $outbox = $shop->outboxes()->where('type', $outboxType)->first();
            //print "==========\n";
            //print_r($outbox);
            //print "==========\n";


            if ($outbox) {


                $sources   = Arr::get($outbox->sources, 'outboxes', []);
                $sources[] = $shop->organisation->id.':'.$auroraOutboxes->{'Email Campaign Type Key'};
                $sources   = array_unique($sources);

                $outbox->updateQuietly([
                    'sources' => [
                        'outboxes' => $sources,
                    ]
                ]);



            }
        }

    }

    private function mapAuroraOutboxCode($auroraCode): OutboxTypeEnum
    {
        return match($auroraCode) {
            'New Customer' => OutboxTypeEnum::NEW_CUSTOMER,
            'AbandonedCart' => OutboxTypeEnum::ABANDONED_CART,
            'Basket Low Stock' => OutboxTypeEnum::BASKET_LOW_STOCK,
            'Basket Reminder 1' => OutboxTypeEnum::BASKET_REMINDER_1,
            'Basket Reminder 2' => OutboxTypeEnum::BASKET_REMINDER_2,
            'Basket Reminder 3' => OutboxTypeEnum::BASKET_REMINDER_3,
            'Delivery Confirmation' => OutboxTypeEnum::DELIVERY_CONFIRMATION,
            'Delivery Note Dispatched' => OutboxTypeEnum::DELIVERY_NOTE_DISPATCHED,
            'Delivery Note Undispatched' => OutboxTypeEnum::DELIVERY_NOTE_UNDISPATCHED,
            'Invite', 'Invite Full Mailshot', 'Invite Mailshot' => OutboxTypeEnum::INVITE,
            'Marketing' => OutboxTypeEnum::MARKETING,
            'New Order' => OutboxTypeEnum::NEW_ORDER,
            'Newsletter' => OutboxTypeEnum::NEWSLETTER,
            'OOS Notification' => OutboxTypeEnum::OOS_NOTIFICATION,
            'Order Confirmation' => OutboxTypeEnum::ORDER_CONFIRMATION,
            'Password Reminder' => OutboxTypeEnum::PASSWORD_REMINDER,
            'Registration' => OutboxTypeEnum::REGISTRATION,
            'Registration Approved' => OutboxTypeEnum::REGISTRATION_APPROVED,
            'Registration Rejected' => OutboxTypeEnum::REGISTRATION_REJECTED,
            'GR Reminder' => OutboxTypeEnum::REORDER_REMINDER,
            'Invoice Deleted' => OutboxTypeEnum::INVOICE_DELETED,
        };
    }


}
