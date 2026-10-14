<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\Procurement\OrgAgent\GetAgentStockCoverBuckets;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Enums\Catalogue\Review\ReviewScopeEnum;
use App\Enums\Catalogue\Review\ReviewStateEnum;
use App\Enums\Catalogue\Review\ReviewStatusEnum;
use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Models\Helpers\Currency;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedSort;

/**
 * The sales lists of an agent in SQL: what each of its supplier products sold at cost (last 12 months, this year up to
 * the last complete month, the same months last year), the worth of the stock at cost and the stock cover verdict, one
 * sub-query per organisation because the verdict embeds that organisation's own lead time, then added up per product
 * and per supplier. Amounts are in the agent's currency and the sales follow ShowAgentSales::COST_OF_SALES.
 */
class GetAgentSalesProducts
{
    use AsObject;

    public const string CHANGE = 'case when last_year > 0 then (this_year / last_year - 1) * 100 end';

    public const string DERIVED = 'case when reviews > 0 then rating_sum / reviews end as rating,
        case when invoices > 0 then 100.0 * refunds / invoices end as refund_percent,
        case when delivery_lines > 0 then 100.0 * short_lines / delivery_lines end as short_percent,
        case when last_year > 0 then (this_year / last_year - 1) * 100 end as change_percent';

    private const array COLUMNS = ['supplier_product_id', 'code', 'name', 'supplier_id', 'supplier_code', 'supplier_name', 'supplier_slug', 'organisation_code', 'sold', 'this_year', 'last_year', 'stock_value', 'bucket', 'invoices', 'refunds', 'reviews', 'rating_sum'];

    /**
     * @return array{0: Currency, 1: float}
     */
    public function agentCurrency(Agent $agent): array
    {
        $organisation = $agent->organisation;
        $toAgent      = (float) GetCurrencyExchange::run($organisation->group->currency, $organisation->currency);

        return [$toAgent ? $organisation->currency : $organisation->group->currency, $toAgent ?: 1.0];
    }

    private function problemSql(string $bucket = 'bucket'): string
    {
        $cases = collect(ShowAgentSales::PROBLEMS)->groupBy(fn (string $problem) => $problem)
            ->map(fn ($buckets, string $problem) => "when $bucket in ('".$buckets->keys()->implode("', '")."') then '$problem'")
            ->implode(' ');

        return "case $cases end";
    }

    private function bucketsOf(string $problem): string
    {
        return "'".collect(ShowAgentSales::PROBLEMS)->filter(fn (string $value) => $value === $problem)->keys()->implode("', '")."'";
    }

    /**
     * What customers publicly said about each org stock. Only product reviews that the website shows (published,
     * public and approved) count, and only for products made of a single org stock: a review of a kit says nothing
     * about one of its parts.
     */
    public function publicReviews(): Builder
    {
        return DB::table('reviews as review')
            ->join('product_has_org_stocks as phos', 'phos.product_id', 'review.product_id')
            ->where('review.scope', ReviewScopeEnum::PRODUCT->value)
            ->where('review.state', ReviewStateEnum::PUBLISHED->value)
            ->where('review.is_public', true)
            ->where('review.review_status', ReviewStatusEnum::APPROVED->value)
            ->whereNull('review.deleted_at')
            ->whereRaw('(select count(*) from product_has_org_stocks parts where parts.product_id = review.product_id) = 1');
    }

    private function reviewsByOrgStock(): Builder
    {
        return $this->publicReviews()
            ->groupBy('phos.org_stock_id')
            ->selectRaw('phos.org_stock_id, count(*) as reviews, sum(review.rating_main) as rating_sum');
    }

    /**
     * Refunds and replacements the supplier is blamed for in the last 12 months, per supplier product. A document
     * counts against a supplier product only when it holds exactly one product of that supplier, otherwise the blame
     * cannot be told apart.
     */
    private function faultsByProduct(Agent $agent): Builder
    {
        $since   = now()->subYear()->toDateTimeString();
        $invoice = DB::table('feedbacks as f')
            ->join('invoice_transactions as it', fn ($join) => $join->on('it.invoice_id', 'f.origin_id')->whereNull('it.deleted_at'))
            ->join('invoice_transaction_has_org_stocks as h', 'h.invoice_transaction_id', 'it.id')
            ->where('f.origin_type', 'Invoice')
            ->where('f.blame_supplier', true)
            ->whereNull('f.deleted_at')
            ->where('f.created_at', '>=', $since)
            ->selectRaw("'I' as kind, f.origin_id as document_id, h.org_stock_id");
        $replacement = DB::table('feedbacks as f')
            ->join('delivery_note_items as i', 'i.delivery_note_id', 'f.origin_id')
            ->where('f.origin_type', 'DeliveryNote')
            ->where('f.blame_supplier', true)
            ->whereNull('f.deleted_at')
            ->where('f.created_at', '>=', $since)
            ->whereNotNull('i.org_stock_id')
            ->selectRaw("'D' as kind, f.origin_id as document_id, i.org_stock_id");

        $attributed = DB::query()->fromSub($invoice->union($replacement), 'doc')
            ->join('org_stock_has_org_supplier_products as link', fn ($join) => $join->on('link.org_stock_id', 'doc.org_stock_id')->where('link.status', true))
            ->join('org_supplier_products as fault_osp', 'fault_osp.id', 'link.org_supplier_product_id')
            ->join('org_agents as fault_oa', fn ($join) => $join->on('fault_oa.id', 'fault_osp.org_agent_id')->where('fault_oa.agent_id', $agent->id))
            ->join('supplier_products as fault_sp', fn ($join) => $join->on('fault_sp.id', 'fault_osp.supplier_product_id')->whereNull('fault_sp.deleted_at'))
            ->groupBy('doc.kind', 'doc.document_id', 'fault_sp.supplier_id')
            ->havingRaw('count(distinct fault_sp.id) = 1')
            ->selectRaw('min(fault_sp.id) as sp_id');

        return DB::query()->fromSub($attributed, 'attributed')->groupBy('sp_id')->selectRaw('sp_id, count(*) as fault_count');
    }

    /**
     * Delivery lines of the last 12 months that arrived short: never received, or checked in below what was ordered.
     * Lines still on their way (the quantity is only filled once checked) are left out of both counts.
     */
    private function shortLinesByProduct(Agent $agent): Builder
    {
        $settled = [StockDeliveryItemStateEnum::NOT_RECEIVED->value, StockDeliveryItemStateEnum::CHECKED->value, StockDeliveryItemStateEnum::PLACED->value];

        return DB::table('stock_delivery_items as line')
            ->join('supplier_products as line_sp', 'line_sp.id', 'line.supplier_product_id')
            ->join('suppliers as line_sup', fn ($join) => $join->on('line_sup.id', 'line_sp.supplier_id')->where('line_sup.agent_id', $agent->id))
            ->whereNull('line.deleted_at')
            ->whereIn('line.state', $settled)
            ->where('line.created_at', '>=', now()->subYear()->toDateTimeString())
            ->groupBy('line.supplier_product_id')
            ->selectRaw(
                'line.supplier_product_id as sp_id, count(*) as delivery_lines,
                count(*) filter (where line.state = ? or line.unit_quantity_checked < line.unit_quantity) as short_lines',
                [StockDeliveryItemStateEnum::NOT_RECEIVED->value]
            );
    }

    /**
     * @return array<int, AllowedSort>
     */
    public function derivedSorts(): array
    {
        return collect(['rating' => 'rating', 'refund_percent' => 'refund_percent', 'short_percent' => 'short_percent', 'change_percent' => 'change_percent'])
            ->map(fn (string $column, string $name) => AllowedSort::callback(
                $name,
                fn ($query, bool $descending) => $query->orderByRaw($column.($descending ? ' desc' : ' asc').' nulls last')
            ))
            ->values()
            ->all();
    }

    private function perOrganisation(Agent $agent, OrgAgent $orgAgent, Currency $currency, float $toAgent, ?int $supplierId): Builder
    {
        $buckets      = GetAgentStockCoverBuckets::make();
        $stockToAgent = (float) (GetCurrencyExchange::run($orgAgent->organisation->currency, $currency) ?: 1);
        $cost         = ShowAgentSales::COST_OF_SALES;
        $year         = now()->year;
        $untilMonth   = sprintf('%02d', now()->month - 1);
        $twelveMonths = now()->startOfMonth()->subMonths(11)->format('Y-m');

        $sold = ShowAgentSales::make()->monthlySales($agent, now()->subYear()->startOfYear(), $supplierId)
            ->where('sold_stock.organisation_id', $orgAgent->organisation_id)
            ->groupBy('ts.org_stock_id')
            ->selectRaw(
                "ts.org_stock_id,
                sum($cost) filter (where r.period >= ?) as sold,
                sum($cost) filter (where r.period between ? and ?) as this_year,
                sum($cost) filter (where r.period between ? and ?) as last_year,
                sum(r.invoices) filter (where r.period >= ?) as invoices,
                sum(r.refunds) filter (where r.period >= ?) as refunds",
                [
                    $twelveMonths,
                    "$year-01", "$year-$untilMonth",
                    ($year - 1).'-01', ($year - 1).'-'.$untilMonth,
                    $twelveMonths,
                    $twelveMonths,
                ]
            );

        return $buckets->scope(DB::table('org_supplier_products as osp'), $orgAgent, false)
            ->join('suppliers as sup', 'sup.id', 'sp.supplier_id')
            ->leftJoinSub($sold, 'sold', 'sold.org_stock_id', '=', 'os.id')
            ->leftJoinSub($this->reviewsByOrgStock(), 'reviewed', 'reviewed.org_stock_id', '=', 'os.id')
            ->whereNotNull('os.id')
            ->when($supplierId, fn ($query) => $query->where('sp.supplier_id', $supplierId))
            ->whereRaw('((osp.state = ? and osp.is_available) or sold.sold > 0)', [OrgSupplierProductStateEnum::ACTIVE->value])
            ->orderBy('os.id')
            ->orderBy('osp.id')
            ->selectRaw(
                'distinct on (os.id) sp.id as supplier_product_id, sp.code, sp.name, sp.supplier_id, sup.code as supplier_code, sup.name as supplier_name, sup.slug as supplier_slug,
                ?::text as organisation_code,
                coalesce(sold.sold, 0) * ? as sold, coalesce(sold.this_year, 0) * ? as this_year, coalesce(sold.last_year, 0) * ? as last_year,
                coalesce(s.stock_value, 0) * ? as stock_value, '.$buckets->bucketSql($orgAgent).' as bucket,
                coalesce(sold.invoices, 0) as invoices, coalesce(sold.refunds, 0) as refunds,
                coalesce(reviewed.reviews, 0) as reviews, coalesce(reviewed.rating_sum, 0) as rating_sum',
                [$orgAgent->organisation->code, $toAgent, $toAgent, $toAgent, $stockToAgent]
            );
    }

    /**
     * One row per organisation and org stock the agent's products sit in.
     */
    public function rows(Agent $agent, ?int $supplierId = null): Builder
    {
        [$currency, $toAgent] = $this->agentCurrency($agent);

        $union = null;
        foreach (OrgAgent::where('agent_id', $agent->id)->with('organisation.currency')->get() as $orgAgent) {
            $rows  = $this->perOrganisation($agent, $orgAgent, $currency, $toAgent, $supplierId);
            $union = $union ? $union->unionAll($rows) : $rows;
        }

        return $union ?? DB::query()->selectRaw(collect(self::COLUMNS)->map(fn (string $column) => "null as $column")->implode(', '))->whereRaw('false');
    }

    /**
     * One row per supplier product, added up over the organisations. The organisations column carries each one's
     * sales, stock and problem for the chips.
     */
    public function products(Agent $agent, ?int $supplierId = null, bool $withOrganisations = true): Builder
    {
        $query = DB::query()->fromSub($this->rows($agent, $supplierId), 'rows')
            ->leftJoinSub($this->faultsByProduct($agent), 'faults', 'faults.sp_id', '=', 'rows.supplier_product_id')
            ->leftJoinSub($this->shortLinesByProduct($agent), 'shortfalls', 'shortfalls.sp_id', '=', 'rows.supplier_product_id')
            ->groupBy('rows.supplier_product_id', 'code', 'name', 'supplier_id', 'supplier_code', 'supplier_name', 'supplier_slug')
            ->selectRaw(
                "rows.supplier_product_id as id, code, name, supplier_id, supplier_code, supplier_name, supplier_slug,
                sum(sold) as sold, sum(this_year) as this_year, sum(last_year) as last_year, sum(stock_value) as stock_value,
                sum(invoices) as invoices, sum(refunds) as refunds, sum(reviews) as reviews, sum(rating_sum) as rating_sum,
                coalesce(max(faults.fault_count), 0) as faults, coalesce(max(shortfalls.short_lines), 0) as short_lines, coalesce(max(shortfalls.delivery_lines), 0) as delivery_lines,
                bool_or(bucket <> 'gone') as live,
                bool_or(bucket = 'out') as has_out,
                bool_or(bucket in (".$this->bucketsOf('low').")) as has_low,
                bool_or(bucket in (".$this->bucketsOf('dead').')) as has_dead'
            );

        if ($withOrganisations) {
            $query->selectRaw("json_agg(json_build_object('code', organisation_code, 'sold', sold, 'stock', stock_value, 'problem', ".$this->problemSql().') order by organisation_code) as organisations');
        }

        return $query;
    }

    /**
     * One row per supplier of the agent, with how many of its products are in trouble in at least one organisation.
     */
    public function suppliers(Agent $agent): Builder
    {
        return DB::query()->fromSub($this->products($agent, null, false), 'products')
            ->groupBy('supplier_id', 'supplier_code', 'supplier_name', 'supplier_slug')
            ->selectRaw(
                'supplier_id as id, supplier_code as code, supplier_name as name, supplier_slug as slug,
                count(*) filter (where live) as products, count(*) filter (where sold > 0) as selling,
                sum(sold) as sold, sum(this_year) as this_year, sum(last_year) as last_year,
                sum(invoices) as invoices, sum(refunds) as refunds, sum(reviews) as reviews, sum(rating_sum) as rating_sum,
                sum(faults) as faults, sum(short_lines) as short_lines, sum(delivery_lines) as delivery_lines,
                count(*) filter (where has_out) as out_of_stock, count(*) filter (where has_low) as low, count(*) filter (where has_dead) as dead'
            );
    }
}
