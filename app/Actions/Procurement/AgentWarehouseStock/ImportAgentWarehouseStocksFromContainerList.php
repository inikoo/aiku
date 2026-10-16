<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentWarehouseStock;

use App\Actions\Procurement\PurchaseOrder\MarkPurchaseOrderStage;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\AgentWarehouseStock;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Replaces an agent's packed stock for one organisation and container with the China agent's container list
 * ("AW CHINA PRODUCT LIST": code, units per carton, cartons ordered), HELP-3961/3962/3963.
 * China fills several containers at once, so they are told apart by their number in the notes, not by next or later.
 * Only lines whose code is filled green are in China's warehouse; the rest are still being made by the supplier,
 * so their purchase order is marked as in production instead.
 */
class ImportAgentWarehouseStocksFromContainerList
{
    use AsAction;

    public string $commandSignature = 'agent:import_container_list {agent : agent organisation slug} {organisation : organisation the stock is for} {file : xlsx or csv container list} {--dry-run}';

    private const string CODE = 'B';
    private const string UNITS_PER_CARTON = 'F';
    private const string CARTONS = 'H';
    private const string IN_WAREHOUSE_FILL = '92D050';

    /**
     * @return array{rows: list<array<string, mixed>>, unmatched: list<string>, in_production: list<array{code: string, units: float, purchase_order: ?string}>}
     */
    public function handle(Agent $agent, Organisation $organisation, string $file, bool $dryRun = false): array
    {
        $rows         = [];
        $unmatched    = [];
        $inProduction = [];
        $container    = preg_match('/AWChina-\d+/i', basename($file), $match) ? $match[0] : pathinfo($file, PATHINFO_FILENAME);
        $sheet        = IOFactory::load($file)->getSheet(0);

        foreach ($sheet->toArray(null, true, false, true) as $rowNumber => $line) {
            $rawCode = trim((string) ($line[self::CODE] ?? ''));
            $cartons = (float) str_replace(',', '', (string) ($line[self::CARTONS] ?? ''));
            if ($rawCode === '' || $cartons <= 0 || !is_numeric(str_replace(',', '', (string) ($line[self::UNITS_PER_CARTON] ?? '')))) {
                continue;
            }

            $supplierProduct = $this->supplierProduct($agent, $organisation, $rawCode);
            if (!$supplierProduct) {
                $unmatched[] = $rawCode;
                continue;
            }

            $units           = $cartons * (float) str_replace(',', '', (string) $line[self::UNITS_PER_CARTON]);
            $purchaseOrderId = $this->latestOrderWithProduct($organisation, $supplierProduct);

            if ($sheet->getStyle(self::CODE.$rowNumber)->getFill()->getStartColor()->getRGB() !== self::IN_WAREHOUSE_FILL) {
                $purchaseOrder  = $purchaseOrderId ? PurchaseOrder::find($purchaseOrderId) : null;
                $inProduction[] = ['code' => $supplierProduct->code, 'units' => $units, 'purchase_order' => $purchaseOrder?->reference];
                if (!$dryRun && $purchaseOrder) {
                    $this->markInProduction($purchaseOrder, "China agent container list $container: still being made by the supplier");
                }
                continue;
            }

            $rows[] = [
                'group_id'            => $agent->group_id,
                'agent_id'            => $agent->id,
                'organisation_id'     => $organisation->id,
                'supplier_product_id' => $supplierProduct->id,
                'state'               => AgentWarehouseStockStateEnum::PACKED_FOR_NEXT_CONTAINER->value,
                'quantity'            => $units,
                'purchase_order_id'   => $purchaseOrderId,
                'notes'               => $container,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        if (!$dryRun) {
            DB::transaction(function () use ($agent, $organisation, $container, $rows) {
                AgentWarehouseStock::where('agent_id', $agent->id)->where('organisation_id', $organisation->id)->where('notes', $container)->whereDoesntHave('receipt')->delete();
                AgentWarehouseStock::insert($rows);
            });
        }

        return ['rows' => $rows, 'unmatched' => $unmatched, 'in_production' => $inProduction];
    }

    public function markInProduction(PurchaseOrder $purchaseOrder, string $reason): void
    {
        if ($purchaseOrder->production_started_at || !in_array($purchaseOrder->state, [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED])) {
            return;
        }

        MarkPurchaseOrderStage::make()->action($purchaseOrder, [
            'field'  => 'production_started_at',
            'date'   => MarkPurchaseOrderStage::today($purchaseOrder->organisation),
            'reason' => $reason,
        ]);
    }

    /**
     * Some codes carry the agent's shelf label in front ("G14 RaffP-02"), so the last word is tried too. When the agent
     * holds the code twice, the product this organisation ordered most wins.
     */
    private function supplierProduct(Agent $agent, Organisation $organisation, string $rawCode): ?SupplierProduct
    {
        foreach (array_unique([$rawCode, last(preg_split('/\s+/', $rawCode))]) as $code) {
            $supplierProduct = SupplierProduct::where('agent_id', $agent->id)
                ->whereRaw('lower(code) = ?', [mb_strtolower($code)])
                ->orderByRaw('(select count(*) from purchase_order_transactions where supplier_product_id = supplier_products.id and organisation_id = ?) desc, id', [$organisation->id])
                ->first();
            if ($supplierProduct) {
                return $supplierProduct;
            }
        }

        return null;
    }

    private function latestOrderWithProduct(Organisation $organisation, SupplierProduct $supplierProduct): ?int
    {
        return DB::table('purchase_orders')
            ->join('purchase_order_transactions', 'purchase_order_transactions.purchase_order_id', 'purchase_orders.id')
            ->where('purchase_orders.organisation_id', $organisation->id)
            ->whereNull('purchase_orders.deleted_at')
            ->where('purchase_order_transactions.supplier_product_id', $supplierProduct->id)
            ->orderByRaw("purchase_orders.state in ('settled', 'cancelled', 'not_received'), purchase_orders.id desc")
            ->value('purchase_orders.id');
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $agent = Organisation::where('slug', $command->argument('agent'))->firstOrFail()->agent;
        if (!$agent) {
            $command->error('Not an agent organisation');

            return 1;
        }

        $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        $result       = $this->handle($agent, $organisation, $command->argument('file'), (bool) $command->option('dry-run'));

        $command->info(count($result['rows']).' rows '.($command->option('dry-run') ? 'would be imported' : 'imported'));
        foreach ($result['in_production'] as $line) {
            $command->line("In production: {$line['code']} {$line['units']} units, purchase order ".($line['purchase_order'] ?? 'none found'));
        }
        foreach ($result['unmatched'] as $code) {
            $command->warn("Skipped $code: no product of the agent with that code");
        }

        return 0;
    }
}
