<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use App\Notifications\PurchaseOrderStageChangedByAgentNotification;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * When an agent's staff, not the buying organisation, changes a stage of an order, its buyer is told.
 */
class NotifyBuyerOfAgentStageChange
{
    use AsAction;

    public function handle(PurchaseOrder $purchaseOrder, ?User $user, string $change): bool
    {
        $buyer = $purchaseOrder->buyer;

        if (!$user || !$buyer || !$buyer->status || $buyer->id === $user->id
            || !$purchaseOrder->isAgentOrder() || $user->authTo("procurement.$purchaseOrder->organisation_id.edit")) {
            return false;
        }

        $buyer->notify(new PurchaseOrderStageChangedByAgentNotification($purchaseOrder, $user->contact_name ?: $user->username, $change));

        return true;
    }
}
