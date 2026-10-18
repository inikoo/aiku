<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\SysAdmin\User\NotifyUserOnChosenChannels;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use App\Notifications\PurchaseOrderBalanceDueNotification;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Approving the QC releases the balance: the buyer and the organisation's accounts are told it can be paid.
 */
class NotifyPurchaseOrderBalanceDue
{
    use AsAction;

    public function handle(PurchaseOrder $purchaseOrder, ?User $approver): int
    {
        $left = SyncPurchaseOrderDepositPaid::leftToPay($purchaseOrder);
        if ($left <= 0) {
            return 0;
        }

        $recipients = User::permission(["accounting.$purchaseOrder->organisation_id.edit"])->where('status', true)->get()
            ->push($purchaseOrder->buyer)
            ->filter(fn (?User $user) => $user && $user->status && $user->id !== $approver?->id)
            ->unique('id');

        $balance = $purchaseOrder->currency?->code.' '.number_format($left, 2);
        foreach ($recipients as $recipient) {
            $notification = new PurchaseOrderBalanceDueNotification($purchaseOrder, $balance);
            NotifyUserOnChosenChannels::run(
                $recipient,
                UserNotificationEnum::PURCHASE_ORDER_BALANCE_DUE,
                $notification,
                $notification->title(),
                __('Record the payment on the order with its bank statement.'),
                route($notification->route()['name'], $notification->route()['parameters'])
            );
        }

        return $recipients->count();
    }
}
