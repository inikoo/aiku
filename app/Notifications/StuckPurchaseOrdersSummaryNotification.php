<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StuckPurchaseOrdersSummaryNotification extends Notification
{
    /**
     * @param array<int, array{reference: string, parent: ?string, organisation: ?string, waiting: ?string, days_overdue: int, buyer: ?string, url: string}> $orders
     */
    public function __construct(public array $orders, public bool $ownOrdersOnly)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject(trans_choice(':count purchase order is past its expected date|:count purchase orders are past their expected date', count($this->orders), ['count' => count($this->orders)]))
            ->line($this->ownOrdersOnly
                ? __('Your purchase orders that are past the expected date of their current stage, most late first:')
                : __('Every purchase order past the expected date of its current stage, most late first:'));

        foreach ($this->orders as $order) {
            $message->line(__(':reference (:parent, :organisation): :waiting, :days days late:buyer', [
                'reference'    => "[{$order['reference']}]({$order['url']})",
                'parent'       => $order['parent'] ?? '-',
                'organisation' => $order['organisation'] ?? '-',
                'waiting'      => $order['waiting'] ?? '-',
                'days'         => $order['days_overdue'],
                'buyer'        => !$this->ownOrdersOnly && $order['buyer'] ? ' · '.$order['buyer'] : '',
            ]));
        }

        return $message
            ->action(__('Open the PO journey board'), route('grp.supply-chain.dashboard', ['status' => 'overdue']))
            ->line(__('Sent every Monday to the supply chain managers and to the buyer of each order.'));
    }
}
