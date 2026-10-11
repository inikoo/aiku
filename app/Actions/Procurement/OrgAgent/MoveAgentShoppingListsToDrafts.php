<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\Procurement\ShoppingListItem\CherryPickShoppingListItems;
use App\Enums\Procurement\ShoppingListItem\ShoppingListItemStateEnum;
use App\Models\Procurement\ShoppingListItem;
use App\Models\SupplyChain\Agent;
use Illuminate\Console\Command;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class MoveAgentShoppingListsToDrafts
{
    use AsAction;

    public string $commandSignature = 'procurement:move-agent-shopping-lists-to-drafts {--dry-run : only count what would move}';

    /**
     * Buyers no longer keep a shopping list for an agent, what they want goes straight on the
     * supplier's draft purchase order. This moves the lines still open on those lists onto drafts,
     * once, the same way the agent used to pick them.
     *
     * @return array{picked: int, skipped: int}
     */
    public function handle(Agent $agent, bool $dryRun = false): array
    {
        $lines = ShoppingListItem::where('agent_id', $agent->id)
            ->where('state', ShoppingListItemStateEnum::OPEN)
            ->pluck('id')
            ->map(fn (int $id) => ['id' => $id])
            ->all();

        if ($dryRun || !$lines) {
            return ['picked' => count($lines), 'skipped' => 0];
        }

        $result = CherryPickShoppingListItems::make()->action($agent, $lines);

        return ['picked' => $result['picked'], 'skipped' => count($result['skipped'])];
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $dryRun = (bool) $command->option('dry-run');

        foreach (Agent::whereIn('id', ShoppingListItem::where('state', ShoppingListItemStateEnum::OPEN)->whereNotNull('agent_id')->distinct()->pluck('agent_id'))->get() as $agent) {
            $result = $this->handle($agent, $dryRun);
            $command->info(($dryRun ? 'Would move' : 'Moved')." {$result['picked']} lines for agent $agent->code, {$result['skipped']} skipped");
        }

        return 0;
    }
}
