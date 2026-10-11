<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Enums\Goods\Stock\StockStateEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Models\Procurement\OrgSupplierProduct;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class DiscontinueDeadOrgSupplierProducts
{
    use AsAction;

    public const int MONTHS_WITHOUT_ORDERS = 24;

    public string $commandSignature = 'procurement:discontinue-dead-org-supplier-products
        {--dry-run : only count what would be discontinued, per organisation and agent}
        {--organisation= : organisation slug, all organisations when left out}';

    /**
     * Supplier products bought through an agent that an organisation still holds as active though
     * they are dead: every SKO they supply is discontinued, the organisation stocks none of them, and
     * nobody in the group has ordered one in two years. Their price and availability have not been
     * looked at since, so "active" is a lie that fills the catalogue with noise.
     *
     * Only the organisation's own row changes. The supplier product is shared by the group, and
     * discontinuing it there would hide it from an organisation that still buys it.
     */
    public function dead(?string $organisationSlug = null): Builder
    {
        return DB::table('org_supplier_products as osp')
            ->join('supplier_products as sp', 'sp.id', 'osp.supplier_product_id')
            ->join('organisations as o', 'o.id', 'osp.organisation_id')
            ->join('org_agents as oa', 'oa.id', 'osp.org_agent_id')
            ->join('agents as a', 'a.id', 'oa.agent_id')
            ->where('osp.state', OrgSupplierProductStateEnum::ACTIVE->value)
            ->whereNull('sp.deleted_at')
            ->when($organisationSlug, fn (Builder $query) => $query->where('o.slug', $organisationSlug))
            ->whereExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('stock_has_supplier_products as shs')
                ->whereColumn('shs.supplier_product_id', 'sp.id'))
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('stock_has_supplier_products as shs')
                ->join('stocks as st', 'st.id', 'shs.stock_id')
                ->whereColumn('shs.supplier_product_id', 'sp.id')
                ->where('st.state', '!=', StockStateEnum::DISCONTINUED->value))
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('org_stock_has_org_supplier_products as link')
                ->join('org_stocks as os', 'os.id', 'link.org_stock_id')
                ->whereColumn('link.org_supplier_product_id', 'osp.id')
                ->where('link.status', true)
                ->where('os.state', OrgStockStateEnum::ACTIVE->value))
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('purchase_order_transactions as pot')
                ->join('purchase_orders as po', 'po.id', 'pot.purchase_order_id')
                ->whereColumn('pot.supplier_product_id', 'sp.id')
                ->whereNull('pot.deleted_at')
                ->whereNull('po.deleted_at')
                ->whereRaw('coalesce(po.submitted_at, po.date, po.created_at) >= ?', [now()->subMonths(self::MONTHS_WITHOUT_ORDERS)]));
    }

    /**
     * @return array<int, array{organisation: string, agent: string, products: int}>
     */
    public function preview(?string $organisationSlug = null): array
    {
        return $this->dead($organisationSlug)
            ->groupBy('o.code', 'a.code')
            ->orderBy('o.code')
            ->orderBy('a.code')
            ->selectRaw('o.code as organisation, a.code as agent, count(*) as products')
            ->get()
            ->map(fn ($row) => ['organisation' => $row->organisation, 'agent' => $row->agent, 'products' => (int) $row->products])
            ->all();
    }

    public function handle(?string $organisationSlug = null): int
    {
        $discontinued = 0;

        $this->dead($organisationSlug)->orderBy('osp.id')->select('osp.id')->chunkById(500, function ($rows) use (&$discontinued) {
            foreach (OrgSupplierProduct::whereIn('id', $rows->pluck('id'))->get() as $orgSupplierProduct) {
                UpdateOrgSupplierProduct::make()->action($orgSupplierProduct, ['state' => OrgSupplierProductStateEnum::DISCONTINUED->value]);
                $discontinued++;
            }
        }, 'osp.id', 'id');

        return $discontinued;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $organisationSlug = $command->option('organisation');
        $preview          = $this->preview($organisationSlug);

        $command->table(['Organisation', 'Agent', 'Dead supplier products'], $preview);
        $command->info('Total: '.array_sum(array_column($preview, 'products')));

        if ($command->option('dry-run')) {
            $command->info('Dry run, nothing changed');

            return 0;
        }

        $command->info('Discontinued '.$this->handle($organisationSlug).' for their organisation');

        return 0;
    }
}
