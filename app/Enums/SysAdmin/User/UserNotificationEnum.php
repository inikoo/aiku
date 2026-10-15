<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 14 Sep 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\User;

use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;

enum UserNotificationEnum: string
{
    case TICKET_RESOLVED = 'ticket_resolved';
    case TICKET_COMMENT = 'ticket_comment';
    case TICKET_MENTION = 'ticket_mention';
    case TICKET_NEEDS_REPLY = 'ticket_needs_reply';
    case PURCHASE_ORDER_STAGE_BY_AGENT = 'purchase_order_stage_by_agent';
    case PURCHASE_ORDERS_STUCK = 'purchase_orders_stuck';
    case PURCHASE_ORDER_COMPLIANCE_DUE = 'purchase_order_compliance_due';
    case PURCHASE_ORDER_MINIMUM_POKE = 'purchase_order_minimum_poke';
    case PURCHASE_ORDER_BALANCE_DUE = 'purchase_order_balance_due';

    public const array PROCUREMENT_EVENTS = [
        self::PURCHASE_ORDER_STAGE_BY_AGENT,
        self::PURCHASE_ORDERS_STUCK,
        self::PURCHASE_ORDER_COMPLIANCE_DUE,
        self::PURCHASE_ORDER_MINIMUM_POKE,
        self::PURCHASE_ORDER_BALANCE_DUE,
    ];

    public const array CHANNELS = ['email', 'slack', 'browser'];

    public function label(): string
    {
        return match ($this) {
            self::TICKET_RESOLVED    => __('My ticket is resolved'),
            self::TICKET_COMMENT     => __('New comment on my ticket'),
            self::TICKET_MENTION     => __('Someone @mentions me on a ticket'),
            self::TICKET_NEEDS_REPLY => __('My ticket needs more information from me'),
            self::PURCHASE_ORDER_STAGE_BY_AGENT => __('An agent moves one of my purchase orders to another stage'),
            self::PURCHASE_ORDERS_STUCK         => __('The weekly list of purchase orders past their expected date'),
            self::PURCHASE_ORDER_COMPLIANCE_DUE => __('Compliance documents of my purchase order are due'),
            self::PURCHASE_ORDER_BALANCE_DUE    => __('A purchase order passed its QC and its balance can be paid'),
            self::PURCHASE_ORDER_MINIMUM_POKE   => __('Another organisation asks me to add to my draft to reach a supplier minimum (email and Slack come grouped every 15 minutes)'),
        };
    }

    /**
     * @return array<int, string>
     */
    public function channelsFor(User $user): array
    {
        $chosen = Arr::get($user->settings, 'notifications.'.$this->value);
        if (is_array($chosen)) {
            return array_values(array_intersect($chosen, self::CHANNELS));
        }

        if ($this === self::TICKET_COMMENT) {
            return [];
        }

        if (in_array($this, self::PROCUREMENT_EVENTS, true)) {
            return ['email'];
        }

        return match (Arr::get($user->settings, 'ticket_notifications', 'both')) {
            'email' => ['email'],
            'slack' => ['slack'],
            'none'  => [],
            default => ['email', 'slack'],
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TICKET_RESOLVED    => 'fal fa-check-double',
            self::TICKET_COMMENT     => 'fal fa-comment-lines',
            self::TICKET_MENTION     => 'fal fa-at',
            self::TICKET_NEEDS_REPLY => 'fal fa-question-circle',
            self::PURCHASE_ORDER_STAGE_BY_AGENT => 'fal fa-truck-loading',
            self::PURCHASE_ORDERS_STUCK         => 'fal fa-hourglass-half',
            self::PURCHASE_ORDER_COMPLIANCE_DUE => 'fal fa-clipboard-check',
            self::PURCHASE_ORDER_BALANCE_DUE    => 'fal fa-file-invoice',
            self::PURCHASE_ORDER_MINIMUM_POKE   => 'fal fa-hand-point-right',
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function valuesFor(User $user): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $event) => [$event->value => $event->channelsFor($user)])->all();
    }

    /**
     * @return array<int, array{value: string, label: string, icon: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn (self $event) => ['value' => $event->value, 'label' => $event->label(), 'icon' => $event->icon()])->all();
    }
}
