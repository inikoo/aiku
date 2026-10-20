<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 12:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Procurement;

use App\Actions\Procurement\OrgAgent\Hydrators\OrgAgentHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * HELP-4104: purchase orders an organisation placed directly with a supplier that belongs to an agent
 * (before orders went through agents) are linked to that agent, so the agent organisation lists and
 * opens them. Drafts stay direct: a linked draft would hold back the other organisations' agent orders
 * to the same supplier. Only agent_id changes: no agent order reference, no amounts, no stock deliveries.
 * Each linked order keeps data->agent_linked_at so the run can be told apart and undone.
 */
class LinkAgentSupplierPurchaseOrders
{
    use AsAction;

    public string $commandSignature = 'procurement:link_agent_supplier_purchase_orders {agent : agent slug} {--dry-run}';

    /**
     * @return Collection<int, PurchaseOrder>
     */
    public function handle(Agent $agent, bool $dryRun = false): Collection
    {
        $purchaseOrders = PurchaseOrder::query()
            ->where('parent_type', 'OrgSupplier')
            ->whereNull('agent_id')
            ->where('state', '!=', PurchaseOrderStateEnum::IN_PROCESS)
            ->whereIn('supplier_id', Supplier::where('agent_id', $agent->id)->select('id'))
            ->whereIn('organisation_id', $agent->orgAgents()->select('organisation_id'))
            ->with('organisation:id,code')
            ->orderBy('date')
            ->get(['id', 'reference', 'organisation_id', 'state', 'date', 'supplier_id']);

        if ($dryRun || $purchaseOrders->isEmpty()) {
            return $purchaseOrders;
        }

        DB::transaction(fn () => PurchaseOrder::whereIn('id', $purchaseOrders->pluck('id'))
            ->whereNull('agent_id')
            ->update([
                'agent_id' => $agent->id,
                'data'     => DB::raw("jsonb_set(coalesce(data, '{}'::jsonb), '{agent_linked_at}', to_jsonb(now()::text))"),
            ]));

        foreach ($agent->orgAgents()->whereIn('organisation_id', $purchaseOrders->pluck('organisation_id')->unique())->get() as $orgAgent) {
            OrgAgentHydratePurchaseOrders::run($orgAgent);
        }
        AgentHydratePurchaseOrders::run($agent);

        return $purchaseOrders;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $agent = Agent::where('slug', $command->argument('agent'))->firstOrFail();

        $dryRun         = (bool) $command->option('dry-run');
        $purchaseOrders = $this->handle($agent, $dryRun);

        $command->table(
            ['Organisation', 'Reference', 'State', 'Date'],
            $purchaseOrders->map(fn (PurchaseOrder $purchaseOrder) => [$purchaseOrder->organisation->code, $purchaseOrder->reference, $purchaseOrder->state->value, $purchaseOrder->date?->toDateString()])->all()
        );
        $command->info(($dryRun ? 'Dry run, nothing written. Would link ' : 'Linked ').$purchaseOrders->count().' purchase orders to '.$agent->name);

        return 0;
    }
}
