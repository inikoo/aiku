<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\WithAgentShoppingSubNavigation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\OrgAgent\OrgAgentOrderContainerEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Helpers\Currency;
use App\Models\Procurement\OrgAgent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAgentDraftsToSend extends OrgAction
{
    use WithAgentShoppingSubNavigation;
    use WithProcurementAuthorisation;

    public const array VIEWS = ['cards', 'orders'];

    private OrgAgent $orgAgent;

    private string $view;

    /**
     * Draft purchase orders through this agent that hold at least one product, every organisation of
     * the group included: a supplier is sent to the agent as one job, so each organisation has to see
     * what the others are stacking on it.
     */
    public static function drafts(OrgAgent $orgAgent): Builder
    {
        return DB::table('purchase_orders as po')
            ->where('po.group_id', $orgAgent->group_id)
            ->where('po.agent_id', $orgAgent->agent_id)
            ->where('po.parent_type', 'OrgSupplier')
            ->where('po.state', PurchaseOrderStateEnum::IN_PROCESS->value)
            ->whereNull('po.deleted_at')
            ->whereExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('purchase_order_transactions as line')
                ->whereColumn('line.purchase_order_id', 'po.id')
                ->whereNull('line.deleted_at'));
    }

    /**
     * @return array<int, array<string, mixed>> one card per supplier, each with a row per organisation
     */
    public function handle(OrgAgent $orgAgent): array
    {
        $rows = self::drafts($orgAgent)
            ->join('purchase_order_transactions as pot', fn ($join) => $join->on('pot.purchase_order_id', 'po.id')->whereNull('pot.deleted_at'))
            ->join('supplier_products as sp', 'sp.id', 'pot.supplier_product_id')
            ->join('organisations as o', 'o.id', 'po.organisation_id')
            ->join('suppliers as sup', 'sup.id', 'po.supplier_id')
            ->leftJoin('currencies as cur', 'cur.id', 'po.currency_id')
            ->groupBy('po.id', 'o.id', 'sup.id', 'cur.id')
            ->orderBy('sup.code')
            ->orderBy('o.code')
            ->selectRaw("po.id, po.slug, po.reference, po.organisation_id, po.data->'ready_to_send' as ready_to_send, po.data->'others_asked' as others_asked,
                o.code as organisation_code, o.name as organisation_name, o.slug as organisation_slug,
                sup.id as supplier_id, sup.code as supplier_code, sup.name as supplier_name, cur.code as currency, cur.id as currency_id,
                count(pot.id) as lines,
                json_agg(distinct pot.supplier_product_id) as supplier_product_ids,
                coalesce(sum(pot.net_amount), 0) as amount,
                sum(pot.quantity_ordered / nullif(sp.units_per_carton, 0) * sp.cbm) as cbm,
                count(*) filter (where sp.cbm is null or coalesce(sp.units_per_carton, 0) = 0) as lines_without_cbm")
            ->get();

        $organisationCurrency = $orgAgent->organisation->currency;
        $exchanges            = $rows->pluck('currency_id')->filter()->unique()->mapWithKeys(fn (int $currencyId) => [
            $currencyId => $currencyId === $organisationCurrency->id ? null : GetCurrencyExchange::run(Currency::find($currencyId), $organisationCurrency),
        ]);

        return $rows->groupBy('supplier_id')->map(function ($drafts) use ($orgAgent, $exchanges) {
            $parts = $drafts->map(function ($draft) use ($orgAgent) {
                $ready = json_decode($draft->ready_to_send ?? 'null', true);

                return [
                    'purchase_order_id' => $draft->id,
                    'reference'         => $draft->reference,
                    'organisation_code' => $draft->organisation_code,
                    'organisation_name' => $draft->organisation_name,
                    'is_ours'           => $draft->organisation_id === $orgAgent->organisation_id,
                    'lines'             => (int) $draft->lines,
                    'amount'            => (float) $draft->amount,
                    'cbm'               => $draft->cbm !== null ? round((float) $draft->cbm, 2) : null,
                    'lines_without_cbm' => (int) $draft->lines_without_cbm,
                    'ready'             => $ready !== null,
                    'ready_by'          => $ready['by'] ?? null,
                    'ready_at'          => $ready['at'] ?? null,
                    'url'               => $draft->organisation_id === $orgAgent->organisation_id
                        ? route('grp.org.procurement.org_agents.show.purchase-orders.show', [$draft->organisation_slug, $orgAgent->slug, $draft->slug])
                        : null,
                ];
            })->values();

            return [
                'supplier_id'       => $drafts->first()->supplier_id,
                'supplier_code'     => $drafts->first()->supplier_code,
                'supplier_name'     => $drafts->first()->supplier_name,
                'currency'          => $drafts->first()->currency,
                'org_exchange'      => $exchanges->get($drafts->first()->currency_id),
                'lines'             => $drafts->flatMap(fn ($draft) => json_decode($draft->supplier_product_ids, true))->unique()->count(),
                'amount'            => round($parts->sum('amount'), 2),
                'cbm'               => round($parts->sum('cbm'), 2),
                'lines_without_cbm' => $parts->sum('lines_without_cbm'),
                'ready_count'       => $parts->where('ready', true)->count(),
                'all_ready'         => $parts->every(fn (array $part) => $part['ready']),
                'has_ours'          => $parts->contains(fn (array $part) => $part['is_ours']),
                'others_asked'      => json_decode($drafts->firstWhere('organisation_id', $orgAgent->organisation_id)?->others_asked ?? 'null', true),
                'parts'             => $parts->all(),
            ];
        })->values()->all();
    }

    /**
     * Every product on the drafts, keyed by purchase order, shown when a purchase order is opened in the list, and on the cards only when a part's products are asked for.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function lines(OrgAgent $orgAgent): array
    {
        $lines = self::drafts($orgAgent)
            ->join('purchase_order_transactions as pot', fn ($join) => $join->on('pot.purchase_order_id', 'po.id')->whereNull('pot.deleted_at'))
            ->join('supplier_products as sp', 'sp.id', 'pot.supplier_product_id')
            ->leftJoin('org_stocks as os', 'os.id', 'pot.org_stock_id')
            ->orderBy('sp.code')
            ->selectRaw('pot.id, pot.purchase_order_id, pot.org_supplier_product_id, sp.code, sp.name, pot.quantity_ordered, sp.units_per_carton, coalesce(nullif(os.packed_in, 0), sp.units_per_pack) as units_per_sko, pot.unit_cost, pot.net_amount,
                pot.quantity_ordered / nullif(sp.units_per_carton, 0) * sp.cbm as cbm')
            ->get();

        $images = DB::table('org_stock_has_org_supplier_products as link')
            ->join('product_has_org_stocks as phos', 'phos.org_stock_id', 'link.org_stock_id')
            ->join('products', 'products.id', 'phos.product_id')
            ->whereIn('link.org_supplier_product_id', $lines->pluck('org_supplier_product_id')->filter()->unique()->values())
            ->whereNull('products.deleted_at')
            ->whereNotNull('products.web_images->main->thumbnail')
            ->selectRaw("distinct on (link.org_supplier_product_id) link.org_supplier_product_id, products.web_images->'main'->>'thumbnail' as thumbnail")
            ->orderBy('link.org_supplier_product_id')
            ->orderByDesc('products.id')
            ->pluck('thumbnail', 'org_supplier_product_id')
            ->map(fn (string $thumbnail) => json_decode($thumbnail, true));

        return $lines
            ->groupBy('purchase_order_id')
            ->map(fn ($lines) => $lines->map(fn ($line) => [
                'id'       => $line->id,
                'image'    => $images->get($line->org_supplier_product_id),
                'code'     => $line->code,
                'name'     => $line->name,
                'units'    => (float) $line->quantity_ordered,
                'cartons'  => $line->units_per_carton ? round((float) $line->quantity_ordered / (int) $line->units_per_carton, 2) : null,
                'skos'     => $line->units_per_sko ? round((float) $line->quantity_ordered / (float) $line->units_per_sko, 2) : null,
                'unit_cost' => $line->unit_cost !== null ? (float) $line->unit_cost : null,
                'amount'   => (float) $line->net_amount,
                'cbm'      => $line->cbm !== null ? round((float) $line->cbm, 3) : null,
            ])->all())
            ->all();
    }

    /**
     * The view the user last chose is theirs to keep: picking a tab saves it on the user, and the
     * page opens on it next time, on any device.
     */
    private function rememberedView(ActionRequest $request): string
    {
        $user  = $request->user();
        $saved = Arr::get($user->settings, 'views.agent_drafts_to_send');
        $asked = $request->input('view');

        if (in_array($asked, self::VIEWS, true)) {
            if ($asked !== $saved) {
                $settings = $user->settings ?? [];
                Arr::set($settings, 'views.agent_drafts_to_send', $asked);
                $user->updateQuietly(['settings' => $settings]);
            }

            return $asked;
        }

        return in_array($saved, self::VIEWS, true) ? $saved : 'cards';
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): array
    {
        $this->orgAgent = $orgAgent;
        $this->initialisation($organisation, $request);
        $this->view = $this->rememberedView($request);

        return $this->handle($orgAgent);
    }

    public function htmlResponse(array $cards, ActionRequest $request): Response
    {
        $title = __('Purchase orders to submit');

        return Inertia::render(
            'Procurement/AgentDraftsToSend',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->orgAgent, $request->route()->originalParameters()),
                'title'       => $title,
                'pageHead'    => [
                    'icon'          => ['icon' => ['fal', 'fa-paper-plane'], 'title' => $title],
                    'model'         => $this->orgAgent->agent->name,
                    'title'         => $title,
                    'subNavigation' => $this->getAgentShoppingNavigation($this->orgAgent),
                ],
                'agentName'   => $this->orgAgent->agent->name,
                'orgCurrency' => $this->organisation->currency->code,
                'container'   => [
                    'label' => OrgAgentOrderContainerEnum::labels()[$this->orgAgent->order_container->value],
                    'cbm'   => $this->orgAgent->order_container->cbm(),
                    'short' => str_replace(['ft_hc', 'ft'], [' ft HC', ' ft'], $this->orgAgent->order_container->value),
                ],
                'canSend'     => $request->user()->authTo("procurement.{$this->organisation->id}.edit"),
                'tellRoute'   => [
                    'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.tell',
                    'parameters' => [$this->organisation->slug, $this->orgAgent->slug],
                ],
                'sendRoute'   => [
                    'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.send',
                    'parameters' => [$this->organisation->slug, $this->orgAgent->slug],
                ],
                'tabs'        => [
                    'current'    => $this->view,
                    'navigation' => [
                        'cards'  => ['title' => __('Cards'), 'icon' => 'fal fa-th-large'],
                        'orders' => ['title' => __('By purchase order'), 'icon' => 'fal fa-th-list'],
                    ],
                ],
                'lines'       => $this->view === 'orders' ? fn () => $this->lines($this->orgAgent) : Inertia::optional(fn () => $this->lines($this->orgAgent)),
                'cards'       => $cards,
            ]
        );
    }

    public function getBreadcrumbs(OrgAgent $orgAgent, array $routeParameters): array
    {
        return array_merge(
            ShowAgentShoppingDashboard::make()->getBreadcrumbs($orgAgent, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('POs to submit'),
                        'icon'  => 'fal fa-paper-plane',
                    ],
                ],
            ]
        );
    }
}
