<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\Outbox;

use App\Enums\EnumHelperTrait;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;

enum OutboxTypeEnum: string
{
    use EnumHelperTrait;

    case BASKET_LOW_STOCK           = 'basket_low_stock';
    case BASKET_REMINDER_1          = 'basket_reminder_1';
    case BASKET_REMINDER_2          = 'basket_reminder_2';
    case BASKET_REMINDER_3          = 'basket_reminder_3';
    case NEW_CUSTOMER               = 'new_customer';
    case DELIVERY_NOTE_DISPATCHED   = 'delivery_note_dispatched';
    case DELIVERY_NOTE_UNDISPATCHED = 'delivery_note_undispatched';
    case INVOICE_DELETED            = 'invoice_deleted';
    case NEW_ORDER                  = 'new_order';
    case ABANDONED_CART             = 'abandoned_cart';
    case DELIVERY_CONFIRMATION      = 'delivery_confirmation';
    case REORDER_REMINDER           = 'reorder_reminder';
    case INVITE                     = 'invite';
    case INVITE_MAILSHOT            = 'invite_mailshot';
    case INVITE_FULL_MAILSHOT       = 'invite_full_mailshot';
    case MARKETING                  = 'marketing';
    case NEWSLETTER                 = 'newsletter';
    case OOS_NOTIFICATION           = 'oos_notification';
    case ORDER_CONFIRMATION         = 'order_confirmation';
    case PASSWORD_REMINDER          = 'password_reminder';
    case REGISTRATION               = 'registration';
    case REGISTRATION_APPROVED      = 'registration_approved';
    case REGISTRATION_REJECTED      = 'registration_rejected';


    public function label(): string
    {
        return match ($this) {
            OutboxTypeEnum::BASKET_LOW_STOCK           => 'Low stock in basket',
            OutboxTypeEnum::BASKET_REMINDER_1          => 'First basket reminder',
            OutboxTypeEnum::BASKET_REMINDER_2          => 'Second basket reminder',
            OutboxTypeEnum::BASKET_REMINDER_3          => 'Third basket reminder',
            OutboxTypeEnum::NEW_CUSTOMER               => 'New customer',
            OutboxTypeEnum::DELIVERY_NOTE_DISPATCHED   => 'Delivery note dispatched',
            OutboxTypeEnum::DELIVERY_NOTE_UNDISPATCHED => 'Delivery note undispatched',
            OutboxTypeEnum::INVOICE_DELETED            => 'Invoice deleted',
            OutboxTypeEnum::NEW_ORDER                  => 'New order',
            OutboxTypeEnum::ABANDONED_CART             => 'Abandoned cart',
            OutboxTypeEnum::DELIVERY_CONFIRMATION      => 'Delivery conformation',
            OutboxTypeEnum::REORDER_REMINDER           => 'Reorder reminder',
            OutboxTypeEnum::INVITE                     => 'Invite',
            OutboxTypeEnum::INVITE_MAILSHOT            => 'Invite mailshot',
            OutboxTypeEnum::INVITE_FULL_MAILSHOT       => 'Invite full mailshot',
            OutboxTypeEnum::MARKETING                  => 'Marketing',
            OutboxTypeEnum::NEWSLETTER                 => 'Newsletter',
            OutboxTypeEnum::OOS_NOTIFICATION           => 'Out of stock notification',
            OutboxTypeEnum::ORDER_CONFIRMATION         => 'Order confirmation',
            OutboxTypeEnum::PASSWORD_REMINDER          => 'Password reminder',
            OutboxTypeEnum::REGISTRATION               => 'Registration',
            OutboxTypeEnum::REGISTRATION_APPROVED      => 'Registration approved',
            OutboxTypeEnum::REGISTRATION_REJECTED      => 'Registration rejected',
        };
    }

    // Here will mark what outboxes are not scoped inside a shop, so can be skipped in StoreShop and use on StoreTenant instead
    public function scope(): string
    {
        return match ($this) {
            default=> 'shop'
        };
    }


    public function mailroomCode(): MailroomCodeEnum
    {
        return match ($this) {
            OutboxTypeEnum::BASKET_LOW_STOCK,
            OutboxTypeEnum::BASKET_REMINDER_1,
            OutboxTypeEnum::BASKET_REMINDER_2,
            OutboxTypeEnum::BASKET_REMINDER_3,
            OutboxTypeEnum::REGISTRATION,
            OutboxTypeEnum::REGISTRATION_APPROVED,
            OutboxTypeEnum::REGISTRATION_REJECTED,
            OutboxTypeEnum::PASSWORD_REMINDER,
            OutboxTypeEnum::DELIVERY_CONFIRMATION,
            OutboxTypeEnum::OOS_NOTIFICATION,
            OutboxTypeEnum::ORDER_CONFIRMATION
            => MailroomCodeEnum::CUSTOMER_NOTIFICATION,

            OutboxTypeEnum::DELIVERY_NOTE_DISPATCHED,
            OutboxTypeEnum::DELIVERY_NOTE_UNDISPATCHED,
            OutboxTypeEnum::INVOICE_DELETED,
            OutboxTypeEnum::NEW_ORDER,
            OutboxTypeEnum::NEW_CUSTOMER
            => MailroomCodeEnum::USER_NOTIFICATION,

            OutboxTypeEnum::INVITE,
            OutboxTypeEnum::INVITE_MAILSHOT,
            OutboxTypeEnum::INVITE_FULL_MAILSHOT,
            OutboxTypeEnum::MARKETING,
            OutboxTypeEnum::NEWSLETTER,
            OutboxTypeEnum::ABANDONED_CART,
            OutboxTypeEnum::REORDER_REMINDER
            => MailroomCodeEnum::MARKETING,
        };
    }
}
