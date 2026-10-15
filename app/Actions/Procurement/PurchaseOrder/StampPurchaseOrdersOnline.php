<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\SupplyChain\UI\ShowSupplyChainPurchaseOrderJourney;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Records the day every sellable product of an order was placed in stock and live on the website, as the PO journey
 * board works it out, so the days from placed to online are kept even when a webpage changes later. Nobody sets it.
 */
class StampPurchaseOrdersOnline
{
    use AsAction;

    public string $commandSignature = 'procurement:stamp_purchase_orders_online';

    public function handle(Group $group): int
    {
        $stamped = 0;

        foreach (ShowSupplyChainPurchaseOrderJourney::make()->ribbons($group) as $ribbon) {
            $online = collect($ribbon['segments'])->firstWhere('key', 'products_online');
            if (!$online || $online['state'] !== 'done' || !$online['done_at']) {
                continue;
            }

            $stamped += PurchaseOrder::where('id', $ribbon['id'])->whereNull('online_at')->update(['online_at' => $online['done_at']]);
        }

        return $stamped;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        foreach (Group::all() as $group) {
            $command->info($group->code.': '.$this->handle($group).' orders online');
        }

        return 0;
    }
}
