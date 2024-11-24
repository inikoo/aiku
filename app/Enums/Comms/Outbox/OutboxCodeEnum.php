<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\Comms\PostRoom\PostRoomCodeEnum;
use App\Enums\EnumHelperTrait;

enum OutboxCodeEnum: string
{
    use EnumHelperTrait;

    case BASKET_LOW_STOCK = 'basket_low_stock';
    case BASKET_REMINDER_1 = 'basket_reminder_1';
    case BASKET_REMINDER_2 = 'basket_reminder_2';
    case BASKET_REMINDER_3 = 'basket_reminder_3';
    case NEW_CUSTOMER = 'new_customer';
    case DELIVERY_NOTE_DISPATCHED = 'delivery_note_dispatched';
    case DELIVERY_NOTE_UNDISPATCHED = 'delivery_note_undispatched';
    case INVOICE_DELETED = 'invoice_deleted';
    case NEW_ORDER = 'new_order';
    case ABANDONED_CART = 'abandoned_cart';
    case DELIVERY_CONFIRMATION = 'delivery_confirmation';
    case REORDER_REMINDER = 'reorder_reminder';
    case MARKETING = 'marketing';
    case NEWSLETTER = 'newsletter';
    case OOS_NOTIFICATION = 'oos_notification';
    case ORDER_CONFIRMATION = 'order_confirmation';
    case PASSWORD_REMINDER = 'password_reminder';
    case REGISTRATION = 'registration';
    case REGISTRATION_APPROVED = 'registration_approved';
    case REGISTRATION_REJECTED = 'registration_rejected';
    case RENTAL_AGREEMENT = 'rental_agreement';
    case PALLET_DELIVERY_PROCESSED = 'pallet_delivery_processed';
    case PALLET_RETURN_DISPATCHED = 'pallet_return_dispatched';
    case INVITE = 'invite';
    case TEST = 'test';


    public function type(): OutboxTypeEnum
    {
        return match ($this) {
            OutboxCodeEnum::NEWSLETTER => OutboxTypeEnum::NEWSLETTER,
            OutboxCodeEnum::MARKETING => OutboxTypeEnum::MARKETING,
            OutboxCodeEnum::INVITE => OutboxTypeEnum::COLD_EMAIL,


            OutboxCodeEnum::REGISTRATION,
            OutboxCodeEnum::REGISTRATION_APPROVED,
            OutboxCodeEnum::REGISTRATION_REJECTED,
            OutboxCodeEnum::PASSWORD_REMINDER,
            OutboxCodeEnum::DELIVERY_CONFIRMATION,
            OutboxCodeEnum::OOS_NOTIFICATION,
            OutboxCodeEnum::ORDER_CONFIRMATION,

            OutboxCodeEnum::RENTAL_AGREEMENT,
            OutboxCodeEnum::PALLET_DELIVERY_PROCESSED,
            OutboxCodeEnum::PALLET_RETURN_DISPATCHED
            => OutboxTypeEnum::TRANSACTIONAL,

            OutboxCodeEnum::BASKET_LOW_STOCK,
            OutboxCodeEnum::BASKET_REMINDER_1,
            OutboxCodeEnum::BASKET_REMINDER_2,
            OutboxCodeEnum::BASKET_REMINDER_3,
            OutboxCodeEnum::ABANDONED_CART,
            OutboxCodeEnum::REORDER_REMINDER => OutboxTypeEnum::NOTIFICATION,
            default => OutboxTypeEnum::APP_COMMS
        };
    }

    public function label(): string
    {
        return match ($this) {
            OutboxCodeEnum::BASKET_LOW_STOCK => 'Low stock in basket',
            OutboxCodeEnum::BASKET_REMINDER_1 => 'First basket reminder',
            OutboxCodeEnum::BASKET_REMINDER_2 => 'Second basket reminder',
            OutboxCodeEnum::BASKET_REMINDER_3 => 'Third basket reminder',
            OutboxCodeEnum::NEW_CUSTOMER => 'New customer',
            OutboxCodeEnum::DELIVERY_NOTE_DISPATCHED => 'Delivery note dispatched',
            OutboxCodeEnum::DELIVERY_NOTE_UNDISPATCHED => 'Delivery note undispatched',
            OutboxCodeEnum::INVOICE_DELETED => 'Invoice deleted',
            OutboxCodeEnum::NEW_ORDER => 'New order',
            OutboxCodeEnum::ABANDONED_CART => 'Abandoned cart',
            OutboxCodeEnum::DELIVERY_CONFIRMATION => 'Delivery confirmation',
            OutboxCodeEnum::REORDER_REMINDER => 'Reorder reminder',
            OutboxCodeEnum::MARKETING => 'Deals',
            OutboxCodeEnum::NEWSLETTER => 'Newsletter',
            OutboxCodeEnum::OOS_NOTIFICATION => 'Out of stock notification',
            OutboxCodeEnum::ORDER_CONFIRMATION => 'Order confirmation',
            OutboxCodeEnum::PASSWORD_REMINDER => 'Password reminder',
            OutboxCodeEnum::REGISTRATION => 'Registration',
            OutboxCodeEnum::REGISTRATION_APPROVED => 'Registration approved',
            OutboxCodeEnum::REGISTRATION_REJECTED => 'Registration rejected',
            OutboxCodeEnum::TEST => __('Test'),
            OutboxCodeEnum::RENTAL_AGREEMENT => 'Rental agreement',
            OutboxCodeEnum::PALLET_DELIVERY_PROCESSED => 'Pallet delivery processed',
            OutboxCodeEnum::PALLET_RETURN_DISPATCHED => 'Pallet return dispatched',
            OutboxCodeEnum::INVITE => 'Invite',
        };
    }


    public function layout(): string
    {
        return match ($this) {
            OutboxCodeEnum::BASKET_LOW_STOCK => 'Low stock in basket',
            OutboxCodeEnum::BASKET_REMINDER_1 => 'First basket reminder',
            OutboxCodeEnum::BASKET_REMINDER_2 => 'Second basket reminder',
            OutboxCodeEnum::BASKET_REMINDER_3 => 'Third basket reminder',
            OutboxCodeEnum::NEW_CUSTOMER => 'New customer',
            OutboxCodeEnum::DELIVERY_NOTE_DISPATCHED => 'Delivery note dispatched',
            OutboxCodeEnum::DELIVERY_NOTE_UNDISPATCHED => 'Delivery note undispatched',
            OutboxCodeEnum::INVOICE_DELETED => 'Invoice deleted',
            OutboxCodeEnum::NEW_ORDER => 'New order',
            OutboxCodeEnum::ABANDONED_CART => 'Abandoned cart',
            OutboxCodeEnum::DELIVERY_CONFIRMATION => 'Delivery conformation',
            OutboxCodeEnum::REORDER_REMINDER => 'Reorder reminder',
            OutboxCodeEnum::MARKETING => 'Deals',
            OutboxCodeEnum::NEWSLETTER => 'Newsletter',
            OutboxCodeEnum::OOS_NOTIFICATION => 'Out of stock notification',
            OutboxCodeEnum::ORDER_CONFIRMATION => 'Order confirmation',
            OutboxCodeEnum::PASSWORD_REMINDER => 'Password reminder',
            OutboxCodeEnum::REGISTRATION => 'Registration',
            OutboxCodeEnum::REGISTRATION_APPROVED => 'Registration approved',
            OutboxCodeEnum::REGISTRATION_REJECTED => 'Registration rejected',
            OutboxCodeEnum::TEST => __('Test'),
            OutboxCodeEnum::RENTAL_AGREEMENT => 'Rental agreement',
            OutboxCodeEnum::PALLET_DELIVERY_PROCESSED => 'Pallet delivery processed',
            OutboxCodeEnum::PALLET_RETURN_DISPATCHED => 'Pallet return dispatched',
            OutboxCodeEnum::INVITE => 'Invite',
        };
    }

    public function scope(): string
    {
        return match ($this) {
            OutboxCodeEnum::TEST => 'Organisation',
            OutboxCodeEnum::PASSWORD_REMINDER,
            OutboxCodeEnum::BASKET_LOW_STOCK,
            OutboxCodeEnum::BASKET_REMINDER_1,
            OutboxCodeEnum::BASKET_REMINDER_2,
            OutboxCodeEnum::BASKET_REMINDER_3,
            OutboxCodeEnum::ABANDONED_CART,
            OutboxCodeEnum::REORDER_REMINDER,
            OutboxCodeEnum::REGISTRATION,
            OutboxCodeEnum::REGISTRATION_APPROVED,
            OutboxCodeEnum::REGISTRATION_REJECTED,
            => 'Website',
            OutboxCodeEnum::RENTAL_AGREEMENT,
            OutboxCodeEnum::PALLET_DELIVERY_PROCESSED,
            OutboxCodeEnum::PALLET_RETURN_DISPATCHED => 'Fulfillment',
            default => 'Shop'
        };
    }

    public function blueprint(): OutboxBlueprintEnum
    {
        return match ($this) {
            OutboxCodeEnum::MARKETING,
            OutboxCodeEnum::NEWSLETTER => OutboxBlueprintEnum::MAILSHOT,
            OutboxCodeEnum::TEST => OutboxBlueprintEnum::TEST,
            OutboxCodeEnum::INVITE => OutboxBlueprintEnum::INVITE,
            default => OutboxBlueprintEnum::EMAIL_TEMPLATE
        };
    }

    public function shopTypes(): array
    {
        return match ($this) {
            OutboxCodeEnum::INVOICE_DELETED,
            OutboxCodeEnum::MARKETING,
            OutboxCodeEnum::NEWSLETTER,
            OutboxCodeEnum::PASSWORD_REMINDER => ['b2b', 'b2c', 'dropshipping', 'fulfilment'],
            OutboxCodeEnum::BASKET_LOW_STOCK,
            OutboxCodeEnum::BASKET_REMINDER_1,
            OutboxCodeEnum::BASKET_REMINDER_2,
            OutboxCodeEnum::BASKET_REMINDER_3,
            OutboxCodeEnum::ABANDONED_CART,
            OutboxCodeEnum::REORDER_REMINDER,
            OutboxCodeEnum::REGISTRATION,
            OutboxCodeEnum::REGISTRATION_APPROVED,
            OutboxCodeEnum::INVITE,
            OutboxCodeEnum::REGISTRATION_REJECTED => ['b2b', 'dropshipping', 'fulfilment'],
            OutboxCodeEnum::NEW_CUSTOMER,
            OutboxCodeEnum::NEW_ORDER,
            OutboxCodeEnum::DELIVERY_NOTE_DISPATCHED,
            OutboxCodeEnum::DELIVERY_NOTE_UNDISPATCHED,
            OutboxCodeEnum::DELIVERY_CONFIRMATION,
            OutboxCodeEnum::ORDER_CONFIRMATION => ['b2b', 'b2c', 'dropshipping'],
            OutboxCodeEnum::OOS_NOTIFICATION => ['b2b', 'dropshipping'],
            default => []
        };
    }


    public function defaultState(): OutboxStateEnum
    {
        return match ($this) {
            OutboxCodeEnum::MARKETING,
            OutboxCodeEnum::NEWSLETTER,
            OutboxCodeEnum::INVITE,
            OutboxCodeEnum::TEST,
            => OutboxStateEnum::ACTIVE,
            default => OutboxStateEnum::IN_PROCESS
        };
    }


    public function postRoomCode(): PostRoomCodeEnum
    {
        return match ($this) {
            OutboxCodeEnum::BASKET_LOW_STOCK,
            OutboxCodeEnum::BASKET_REMINDER_1,
            OutboxCodeEnum::BASKET_REMINDER_2,
            OutboxCodeEnum::BASKET_REMINDER_3,
            OutboxCodeEnum::REGISTRATION,
            OutboxCodeEnum::REGISTRATION_APPROVED,
            OutboxCodeEnum::REGISTRATION_REJECTED,
            OutboxCodeEnum::PASSWORD_REMINDER,
            OutboxCodeEnum::DELIVERY_CONFIRMATION,
            OutboxCodeEnum::OOS_NOTIFICATION,
            OutboxCodeEnum::ORDER_CONFIRMATION,
            OutboxCodeEnum::RENTAL_AGREEMENT,
            OutboxCodeEnum::PALLET_DELIVERY_PROCESSED,
            OutboxCodeEnum::PALLET_RETURN_DISPATCHED
            => PostRoomCodeEnum::CUSTOMER_NOTIFICATION,

            OutboxCodeEnum::DELIVERY_NOTE_DISPATCHED,
            OutboxCodeEnum::DELIVERY_NOTE_UNDISPATCHED,
            OutboxCodeEnum::INVOICE_DELETED,
            OutboxCodeEnum::NEW_ORDER,
            OutboxCodeEnum::NEW_CUSTOMER
            => PostRoomCodeEnum::USER_NOTIFICATION,

            OutboxCodeEnum::INVITE
            => PostRoomCodeEnum::LEADS,

            OutboxCodeEnum::MARKETING,
            OutboxCodeEnum::NEWSLETTER,
            OutboxCodeEnum::ABANDONED_CART,
            OutboxCodeEnum::REORDER_REMINDER
            => PostRoomCodeEnum::MARKETING,

            OutboxCodeEnum::TEST,
            => PostRoomCodeEnum::TESTS,
        };
    }
}