<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\GetAgentStockCoverBuckets;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Helpers\TimeSeries\TimeSeriesFrequencyEnum;
use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * How an agent's products sell in the organisations that buy them: what the organisations sold of them to their own
 * customers each month, and the products in trouble there (out of stock, about to run out, not selling). Goods one
 * organisation passes to another are not a sale, the organisation that receives them sells them. Amounts are in the
 * agent's currency.
 */
class ShowAgentSales extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    private const int MONTHS = 12;

    private const int RANKING = 10;

    private const array PROBLEMS = [
        'out'  => 'out',
        'w1'   => 'low',
        'w2'   => 'low',
        'dead' => 'dead',
    ];

    private function monthlySales(Agent $agent, CarbonInterface $from): Builder
    {
        return DB::table('org_stock_time_series as ts')
            ->join('org_stock_time_series_records as r', 'r.org_stock_time_series_id', 'ts.id')
            ->join('org_stocks as sold_stock', 'sold_stock.id', 'ts.org_stock_id')
            ->where('ts.frequency', TimeSeriesFrequencyEnum::MONTHLY->value)
            ->where('r.period', '>=', $from->format('Y-m'))
            ->whereIn(
                'ts.org_stock_id',
                DB::table('org_stock_has_org_supplier_products as link')
                    ->join('org_supplier_products as linked', 'linked.id', 'link.org_supplier_product_id')
                    ->join('org_agents', 'org_agents.id', 'linked.org_agent_id')
                    ->where('org_agents.agent_id', $agent->id)
                    ->where('link.status', true)
                    ->select('link.org_stock_id')
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Agent $agent): array
    {
        $organisation = $agent->organisation;
        $toAgent      = (float) GetCurrencyExchange::run($organisation->group->currency, $organisation->currency);
        $currency     = $toAgent ? $organisation->currency : $organisation->group->currency;
        $toAgent      = $toAgent ?: 1.0;
        $from         = now()->startOfMonth()->subMonths(self::MONTHS - 1);
        $orgAgents    = OrgAgent::where('agent_id', $agent->id)->with('organisation.currency')->get();

        $year = now()->year;

        $byMonth = $this->monthlySales($agent, now()->subYear()->startOfYear())
            ->selectRaw('sold_stock.organisation_id, r.period as month, sum(r.sales_grp_currency_external) as sales')
            ->groupBy('sold_stock.organisation_id', 'r.period')
            ->get()
            ->groupBy('organisation_id')
            ->map(fn (Collection $rows) => $rows->pluck('sales', 'month'));

        $salesIn = fn (OrgAgent $orgAgent, string $period): float => round($toAgent * (float) ($byMonth->get($orgAgent->organisation_id)[$period] ?? 0), 2);
        $inYear  = fn (OrgAgent $orgAgent, int $ofYear, int $untilMonth): array => collect(range(1, $untilMonth))
            ->map(fn (int $month) => $salesIn($orgAgent, sprintf('%d-%02d', $ofYear, $month)))
            ->all();

        $products      = [];
        $organisations = [];
        foreach ($orgAgents as $orgAgent) {
            $code        = $orgAgent->organisation->code;
            $stockToAgent = (float) (GetCurrencyExchange::run($orgAgent->organisation->currency, $currency) ?: 1);
            $rows        = $this->productRows($agent, $orgAgent, $from);

            $organisations[] = [
                'code'     => $code,
                'name'     => $orgAgent->organisation->name,
                'products' => $rows->where('bucket', '!=', 'gone')->count(),
                'selling'  => $rows->where('sold', '>', 0)->count(),
                'sold'      => round(collect(range(0, self::MONTHS - 1))->sum(fn (int $offset) => $salesIn($orgAgent, $from->copy()->addMonths($offset)->format('Y-m'))), 2),
                'this_year' => $inYear($orgAgent, $year, now()->month),
                'last_year' => $inYear($orgAgent, $year - 1, 12),
                'problems' => collect(['out', 'low', 'dead'])->mapWithKeys(fn (string $problem) => [
                    $problem => $rows->filter(fn ($row) => (self::PROBLEMS[$row->bucket] ?? null) === $problem)->count(),
                ])->all(),
            ];

            foreach ($rows as $row) {
                $products[$row->supplier_product_id] ??= ['code' => $row->code, 'name' => $row->name, 'sold' => 0.0, 'organisations' => []];
                $products[$row->supplier_product_id]['sold'] += $toAgent * (float) $row->sold;
                $products[$row->supplier_product_id]['organisations'][] = [
                    'code'    => $code,
                    'sold'    => round($toAgent * (float) $row->sold, 2),
                    'stock'   => round($stockToAgent * (float) $row->stock_value, 2),
                    'problem' => self::PROBLEMS[$row->bucket] ?? null,
                ];
            }
        }

        $products = collect($products)->map(fn (array $product) => [...$product, 'sold' => round($product['sold'], 2)])->values();

        return [
            'currency'      => $currency->code,
            'from'          => $from->toDateString(),
            'year'          => $year,
            'products'      => $products->count(),
            'selling'       => $products->where('sold', '>', 0)->count(),
            'organisations' => $organisations,
            'top_sellers'   => $products->where('sold', '>', 0)->sortByDesc('sold')->take(self::RANKING)->values()->all(),
            'out'           => $this->ranking($products, 'out', 'sold'),
            'low'           => $this->ranking($products, 'low', 'sold'),
            'dead'          => $this->ranking($products, 'dead', 'stock'),
        ];
    }

    /**
     * One row per org stock the organisation holds of this agent's products, with what it sold in the period, in the
     * group's currency, the value of what it holds, in its own, and the stock cover verdict its own buyers see.
     */
    private function productRows(Agent $agent, OrgAgent $orgAgent, CarbonInterface $from): Collection
    {
        $buckets = GetAgentStockCoverBuckets::make();

        return $buckets->scope(DB::table('org_supplier_products as osp'), $orgAgent, false)
            ->leftJoinSub(
                $this->monthlySales($agent, $from)
                    ->where('sold_stock.organisation_id', $orgAgent->organisation_id)
                    ->groupBy('ts.org_stock_id')
                    ->selectRaw('ts.org_stock_id, sum(r.sales_grp_currency_external) as sold'),
                'sold',
                'sold.org_stock_id',
                '=',
                'os.id'
            )
            ->whereNotNull('os.id')
            ->whereRaw('((osp.state = ? and osp.is_available) or sold.sold > 0)', [OrgSupplierProductStateEnum::ACTIVE->value])
            ->selectRaw('sp.id as supplier_product_id, sp.code, sp.name, os.id as org_stock_id, coalesce(s.stock_value, 0) as stock_value, coalesce(sold.sold, 0) as sold, '.$buckets->bucketSql($orgAgent).' as bucket')
            ->get()
            ->unique('org_stock_id')
            ->values();
    }

    /**
     * The products with a given problem in at least one organisation, the ones that matter most first: by what it sold
     * where the problem is, or by the value of the stock sitting there for the ones that do not sell.
     *
     * @return array{count: int, rows: array<int, array<string, mixed>>}
     */
    private function ranking(Collection $products, string $problem, string $weight): array
    {
        $affected = $products
            ->map(function (array $product) use ($problem, $weight) {
                $where = collect($product['organisations'])->where('problem', $problem)->values();

                return [...$product, 'organisations' => $where->all(), 'weight' => $where->sum($weight)];
            })
            ->filter(fn (array $product) => $product['organisations'] !== []);

        return [
            'count' => $affected->count(),
            'rows'  => $affected->sortByDesc('weight')->take(self::RANKING)->values()->all(),
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): Agent
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);

        return $agent;
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Procurement/AgentSales',
            [
                'title'       => __('Sales'),
                'breadcrumbs' => array_merge(
                    ShowProcurementDashboard::make()->getBreadcrumbs($request->route()->originalParameters()),
                    [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'label' => __('Sales'),
                                'route' => [
                                    'name'       => 'grp.org.agent.sales.dashboard',
                                    'parameters' => [$this->organisation->slug],
                                ],
                            ],
                        ],
                    ]
                ),
                'pageHead'    => [
                    'icon'  => [
                        'title' => __('Sales'),
                        'icon'  => 'fal fa-chart-line',
                    ],
                    'title' => __('Sales'),
                ],
                'sales'       => Inertia::defer(fn () => $this->handle($agent)),
            ]
        );
    }
}
