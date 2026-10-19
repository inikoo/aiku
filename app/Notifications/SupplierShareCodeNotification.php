<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Notifications\Concerns\SendsMailThroughSes;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierShareCodeNotification extends Notification
{
    use SendsMailThroughSes;

    public function __construct(public string $code, public string $groupName)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->newMailMessage()
            ->subject(__('Your code to see your sales with :group', ['group' => $this->groupName]))
            ->line(__('Your code is :code', ['code' => $this->code]))
            ->line(__('It works for 10 minutes. If you did not ask for it, you can ignore this email.'));
    }
}
