<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\User;
use App\Notifications\StockDeliveryReceivedWithoutCostsNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use OwenIt\Auditing\Events\AuditCustom;
use Spatie\Permission\Models\Permission;

/**
 * A delivery received before its costs were entered: the reason the user gave is written in the delivery's
 * history under their name, and the procurement supervisors of the organisation and the group's supply chain
 * managers are told.
 */
class ReportStockDeliveryReceivedWithoutCosts
{
    use AsAction;

    /**
     * @param array<int, string> $missing
     */
    public function handle(StockDelivery $stockDelivery, User $user, array $missing, string $reason): int
    {
        $stockDelivery->auditEvent     = 'received_without_costs';
        $stockDelivery->isCustomEvent  = true;
        $stockDelivery->auditCustomOld = ['missing_costs' => implode(', ', $missing)];
        $stockDelivery->auditCustomNew = ['reason' => $reason];
        Event::dispatch(new AuditCustom($stockDelivery));
        $stockDelivery->isCustomEvent  = false;
        $stockDelivery->auditCustomOld = $stockDelivery->auditCustomNew = [];

        $permissions = Permission::whereIn('name', [
            "org-supervisor.$stockDelivery->organisation_id.procurement",
            'supply-chain',
        ])->pluck('name')->all();

        if (!$permissions) {
            return 0;
        }

        $recipients = User::permission($permissions)
            ->where('status', true)
            ->where('id', '!=', $user->id)
            ->get();

        Notification::send($recipients, new StockDeliveryReceivedWithoutCostsNotification($stockDelivery, $user->contact_name ?: $user->username, implode(', ', $missing), $reason));

        return $recipients->count();
    }
}
