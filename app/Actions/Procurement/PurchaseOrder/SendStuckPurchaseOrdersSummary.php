<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\SupplyChain\UI\ShowSupplyChainPurchaseOrderJourney;
use App\Actions\SysAdmin\User\NotifyUserOnChosenChannels;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use App\Notifications\StuckPurchaseOrdersSummaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Every Monday the purchase orders past the expected date of their current stage, as the PO journey board plans
 * them, go to the supply chain managers (all of them) and to each buyer (their own).
 */
class SendStuckPurchaseOrdersSummary
{
    use AsAction;

    public string $commandSignature = 'procurement:send_stuck_purchase_orders_summary';

    public function handle(Group $group): int
    {
        $stuck = ShowSupplyChainPurchaseOrderJourney::make()->ribbons($group)
            ->where('status', 'overdue')
            ->sortByDesc('days_overdue')
            ->values();

        if ($stuck->isEmpty()) {
            return 0;
        }

        setPermissionsTeamId($group->id);
        $managers = User::permission('supply-chain')->where('status', true)->get();
        foreach ($managers as $manager) {
            $this->send($manager, $stuck, false);
        }

        $sent = $managers->count();
        foreach ($stuck->whereNotNull('buyer_id')->groupBy('buyer_id') as $buyerId => $orders) {
            $buyer = User::where('id', $buyerId)->where('status', true)->first();
            if ($buyer && !$managers->contains('id', $buyer->id)) {
                $this->send($buyer, $orders, true);
                $sent++;
            }
        }

        return $sent;
    }

    private function send(User $user, Collection $orders, bool $ownOrdersOnly): void
    {
        $notification = new StuckPurchaseOrdersSummaryNotification($this->lines($orders), $ownOrdersOnly);

        NotifyUserOnChosenChannels::run(
            $user,
            UserNotificationEnum::PURCHASE_ORDERS_STUCK,
            $notification,
            $notification->title(),
            $orders->take(10)->map(fn (array $ribbon) => $ribbon['reference'].': '.$ribbon['current_label'].', '.$ribbon['days_overdue'].'d')->implode("\n"),
            route('grp.supply-chain.dashboard', ['status' => 'overdue'])
        );
    }

    private function lines(Collection $ribbons): array
    {
        return $ribbons->map(fn (array $ribbon) => [
            'reference'    => $ribbon['reference'],
            'parent'       => $ribbon['parent_code'],
            'organisation' => $ribbon['organisation_code'],
            'waiting'      => $ribbon['current_label'],
            'days_overdue' => $ribbon['days_overdue'],
            'buyer'        => $ribbon['buyer_name'],
            'url'          => route($ribbon['route']['name'], $ribbon['route']['parameters']),
        ])->values()->all();
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        foreach (Group::all() as $group) {
            $command->info($group->code.': '.$this->handle($group).' summaries sent');
        }

        return 0;
    }
}
