<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\SupplyChain\SupplierChangeRequest\DecideSupplierChangeRequest;
use App\Enums\Procurement\PurchaseOrderChase\PurchaseOrderChaseChannelEnum;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestStateEnum;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestTypeEnum;
use App\Models\SupplyChain\SupplierChangeRequest;
use App\Models\SysAdmin\User;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStageEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Helpers\Currency;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * An agent's suppliers seen as follow-up work: the orders to chase today, how the open orders are moving, which
 * suppliers deliver on time and in the right quantities, and which supplier records should be closed or merged.
 * Orders count when placed through the agent, as on its purchase order list, even if their supplier has since left
 * the agent; supplier figures count the agent's own suppliers. The buying company filter narrows orders, spend and
 * deliveries, never the supplier list itself.
 */
class GetAgentSuppliersDashboard
{
    use AsObject;

    public const int UNCONFIRMED_DAYS = 14;

    private const int ON_TIME_GRACE_DAYS = 7;

    private const int CHASE_ROWS = 25;

    private const int RANKING_ROWS = 15;

    private const int PERFORMANCE_MONTHS = 24;

    private const int SPEND_MONTHS = 12;

    private const int DORMANT_MONTHS = 24;

    public function handle(Agent $agent, Organisation $organisation, ?Organisation $buyer = null, ?string $currencyCode = null, ?User $user = null): array
    {
        $groupCurrency = $organisation->group->currency;
        $currencyCodes = array_values(array_unique([$groupCurrency->code, 'EUR', $organisation->currency->code]));
        $currencies    = Currency::whereIn('code', $currencyCodes)->get()->keyBy('code')->sortBy(fn (Currency $currency) => array_search($currency->code, $currencyCodes));
        $currency      = $currencies->get($currencyCode) ?? $groupCurrency;
        $exchange      = GetCurrencyExchange::run($groupCurrency, $currency);
        if (!$exchange) {
            $currency = $groupCurrency;
            $exchange = 1.0;
        }
        $money = fn ($amount) => round((float) $amount * $exchange, 2);

        $suppliers   = $this->getSuppliers($agent);
        $openOrders  = $this->getOpenPurchaseOrders($agent, $buyer);
        $pastOrders  = $this->getRecentPurchaseOrders($agent, $buyer);
        $deliveries  = $this->getQuantityChecks($agent, $buyer)->keyBy('supplier_id');

        $sent        = $openOrders->where('state', '!=', PurchaseOrderStateEnum::IN_PROCESS->value);
        $pastEta     = $sent->where('past_eta', true);
        $unconfirmed = $sent->where('unconfirmed', true);
        $received    = $pastOrders->whereNotNull('received_at');
        $withEta     = $received->whereNotNull('estimated_received_at');
        $checked     = $deliveries->sum('lines');
        $wrong       = $deliveries->sum('wrong');
        $dormant     = $suppliers->where('status', true)->filter(fn ($supplier) => $this->isDormant($supplier));
        $pending     = SupplierChangeRequest::where('agent_id', $agent->id)->where('state', SupplierChangeRequestStateEnum::PENDING)->get(['supplier_id', 'type'])
            ->mapWithKeys(fn (SupplierChangeRequest $request) => [$request->supplier_id => $request->type->value]);

        $route = fn (string $name, array $parameters = [], array $query = []) => [
            'name'       => $name,
            'parameters' => array_filter(['organisation' => $organisation->slug, ...$parameters, '_query' => $query ?: null]),
        ];
        $purchaseOrdersRoute = fn (array $elements) => $route('grp.org.procurement.purchase_orders.index', [], [
            ...collect($elements)->mapWithKeys(fn ($value, $key) => ["elements[$key]" => $value])->all(),
            ...($buyer ? ['elements[organisation]' => $buyer->slug] : []),
            'sort' => 'date',
        ]);
        $supplierRoute = fn (?string $slug) => $slug ? $route('grp.org.procurement.org_suppliers.show', ['orgSupplier' => $slug]) : null;

        $chaseRows   = $pastEta->sortBy('estimated_received_at')->concat($unconfirmed->sortBy('submitted_at'))->take(self::CHASE_ROWS);
        $lastChases  = $this->getLastChases($chaseRows->pluck('id')->all());

        return [
            'permissions' => [
                'can_suggest' => (bool) $user?->authTo(['supply-chain.edit', "procurement.{$organisation->id}.edit"]),
                'can_decide'  => $user && DecideSupplierChangeRequest::canDecide($user),
            ],
            'filters'     => [
                'buyer'      => $buyer?->slug,
                'buyers'     => $agent->orgAgents()->join('organisations', 'organisations.id', '=', 'org_agents.organisation_id')
                    ->orderBy('organisations.id')
                    ->get(['organisations.slug', 'organisations.code', 'organisations.name'])
                    ->map(fn ($buyer) => ['slug' => $buyer->slug, 'code' => $buyer->code, 'name' => $buyer->name])
                    ->values(),
                'currency'   => $currency->code,
                'currencies' => $currencies->keys()->values(),
            ],
            'kpis'        => [
                'active_suppliers' => [
                    'value' => $suppliers->where('status', true)->count(),
                    'route' => $route('grp.org.procurement.org_suppliers.index', [], ['tab' => 'index']),
                ],
                'open'             => [
                    'value'       => $sent->count(),
                    'amount'      => $money($sent->sum('amount')),
                    'without_eta' => $sent->whereNull('estimated_received_at')->count(),
                    'route'       => $purchaseOrdersRoute(['state' => 'submitted,confirmed']),
                ],
                'chase'            => [
                    'value'       => $pastEta->count() + $unconfirmed->count(),
                    'past_eta'    => $pastEta->count(),
                    'unconfirmed' => $unconfirmed->count(),
                    'route'       => $purchaseOrdersRoute(['attention' => 'past_eta,unconfirmed_over_14_days']),
                ],
                'in_transit'       => [
                    'value'  => $sent->where('progress', 'dispatched')->count(),
                    'amount' => $money($sent->where('progress', 'dispatched')->sum('amount')),
                    'route'  => $purchaseOrdersRoute(['progress' => 'dispatched']),
                ],
                'lead_time'        => [
                    'median_days'     => $this->medianDays($received),
                    'on_time'         => $this->percentage($withEta->filter(fn ($order) => $this->isOnTime($order))->count(), $withEta->count()),
                    'purchase_orders' => $received->count(),
                ],
                'quantity_errors'  => [
                    'value'      => $wrong,
                    'checked'    => $checked,
                    'percentage' => $this->percentage($wrong, $checked, 1),
                ],
                'dormant'          => [
                    'value'  => $dormant->count(),
                    'active' => $suppliers->where('status', true)->count(),
                ],
            ],
            'stages'      => collect(PurchaseOrderStageEnum::progressLabels())->map(fn ($label, $key) => [
                'key'    => $key,
                'label'  => $label,
                'value'  => $openOrders->where('progress', $key)->count(),
                'amount' => $money($openOrders->where('progress', $key)->sum('amount')),
                'route'  => $key === 'draft' ? null : $purchaseOrdersRoute(['progress' => $key]),
            ])->values(),
            'ages'        => collect([[0, 30, '< 30'], [30, 60, '30-60'], [60, 90, '60-90'], [90, 120, '90-120'], [120, null, '120+']])
                ->map(fn ($bucket) => [
                    'label' => $bucket[2],
                    'value' => $sent->filter(fn ($order) => $order->days_open >= $bucket[0] && ($bucket[1] === null || $order->days_open < $bucket[1]))->count(),
                ]),
            'chase'       => [
                'total' => $pastEta->count() + $unconfirmed->count(),
                'rows'  => $chaseRows
                    ->map(fn ($order) => [
                        'id'            => $order->id,
                        'reason'        => $order->past_eta ? 'past_eta' : 'unconfirmed',
                        'reference'     => $order->reference,
                        'supplier'      => $order->parent_name,
                        'buyer'         => $order->buyer_code,
                        'days_open'     => (int) $order->days_open,
                        'eta'           => $order->estimated_received_at,
                        'amount'        => $money($order->amount),
                        'route'         => $route('grp.org.procurement.purchase_orders.show', ['purchaseOrder' => $order->slug]),
                        'supplier_route' => $supplierRoute($order->org_supplier_slug),
                        'panel_route'   => $route('grp.org.agent.purchase_orders.chase_panel', ['purchaseOrder' => $order->id]),
                        'last_chase'    => $lastChases->get($order->id),
                    ])
                    ->values(),
            ],
            'top_spend'   => $this->getTopSpend($suppliers, $pastOrders, $deliveries, $money, $supplierRoute),
            'quantity'    => $deliveries->where('wrong', '>', 0)
                ->sortByDesc('wrong')
                ->take(self::RANKING_ROWS)
                ->map(fn ($row) => [
                    'supplier'   => $suppliers->get($row->supplier_id)?->name,
                    'code'       => $suppliers->get($row->supplier_id)?->code,
                    'lines'      => (int) $row->lines,
                    'wrong'      => (int) $row->wrong,
                    'short'      => (int) $row->short,
                    'over'       => (int) $row->over,
                    'percentage' => $this->percentage($row->wrong, $row->lines, 1),
                    'route'      => $supplierRoute($suppliers->get($row->supplier_id)?->org_supplier_slug),
                ])
                ->values(),
            'cleanup'     => [
                'buckets'    => collect([
                    ['key' => 'never', 'label' => __('Never ordered'), 'from' => null, 'to' => null],
                    ['key' => '24_36', 'label' => __('2-3 years ago'), 'from' => 24, 'to' => 36],
                    ['key' => '36_48', 'label' => __('3-4 years ago'), 'from' => 36, 'to' => 48],
                    ['key' => '48', 'label' => __('Over 4 years ago'), 'from' => 48, 'to' => null],
                ])->map(fn ($bucket) => [
                    'key'   => $bucket['key'],
                    'label' => $bucket['label'],
                    'value' => $dormant->filter(fn ($supplier) => $this->dormantBucket($supplier) === $bucket['key'])->count(),
                ]),
                'dormant'    => $dormant->sortBy(fn ($supplier) => $supplier->last_ordered_at ?? '')
                    ->map(fn ($supplier) => [
                        'id'              => $supplier->id,
                        'code'            => $supplier->code,
                        'name'            => $supplier->name,
                        'pending'         => $pending->get($supplier->id),
                        'bucket'          => $this->dormantBucket($supplier),
                        'last_ordered_at' => $supplier->last_ordered_at,
                        'products'        => (int) $supplier->products,
                        'route'           => $supplierRoute($supplier->org_supplier_slug),
                    ])
                    ->values(),
                'duplicates' => $this->getPossibleDuplicates($suppliers->where('status', true), $supplierRoute, $pending),
                'requests'   => $this->getChangeRequests($agent, $supplierRoute),
            ],
            'completeness' => $this->getCompleteness($pastOrders->filter(fn ($order) => Carbon::parse($order->submitted_at)->gt(now()->subMonths(self::SPEND_MONTHS)))),
        ];
    }

    private function getSuppliers(Agent $agent): Collection
    {
        $lastOrders = DB::table('purchase_orders')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('suppliers.agent_id', $agent->id)
            ->whereNull('purchase_orders.deleted_at')
            ->where('purchase_orders.state', '!=', PurchaseOrderStateEnum::CANCELLED->value)
            ->selectRaw('purchase_orders.supplier_id, max(coalesce(purchase_orders.submitted_at, purchase_orders.date)) as last_ordered_at')
            ->groupBy('purchase_orders.supplier_id');

        $firstOrgSuppliers = DB::table('org_suppliers')
            ->join('suppliers', 'suppliers.id', '=', 'org_suppliers.supplier_id')
            ->where('suppliers.agent_id', $agent->id)
            ->selectRaw('distinct on (org_suppliers.supplier_id) org_suppliers.supplier_id, org_suppliers.slug')
            ->orderBy('org_suppliers.supplier_id')
            ->orderBy('org_suppliers.id');

        return DB::table('suppliers')
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', '=', 'suppliers.id')
            ->leftJoinSub($lastOrders, 'last_orders', 'last_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoinSub($firstOrgSuppliers, 'first_org_suppliers', 'first_org_suppliers.supplier_id', '=', 'suppliers.id')
            ->where('suppliers.agent_id', $agent->id)
            ->whereNull('suppliers.deleted_at')
            ->selectRaw(
                'suppliers.id, suppliers.code, suppliers.name, suppliers.email, suppliers.status,
                coalesce(supplier_stats.number_current_supplier_products, 0) as products,
                first_org_suppliers.slug as org_supplier_slug, last_orders.last_ordered_at'
            )
            ->get()
            ->keyBy('id');
    }

    private function purchaseOrders(Agent $agent, ?Organisation $buyer): Builder
    {
        return DB::table('purchase_orders')
            ->where('purchase_orders.agent_id', $agent->id)
            ->where('purchase_orders.parent_type', 'OrgSupplier')
            ->whereNull('purchase_orders.deleted_at')
            ->when($buyer, fn (Builder $query) => $query->where('purchase_orders.organisation_id', $buyer->id));
    }

    private function amountSql(): string
    {
        return "(select coalesce(sum(purchase_order_transactions.grp_net_amount), 0) from purchase_order_transactions
            where purchase_order_transactions.purchase_order_id = purchase_orders.id
                and purchase_order_transactions.deleted_at is null and purchase_order_transactions.state <> 'cancelled') as amount";
    }

    private function getOpenPurchaseOrders(Agent $agent, ?Organisation $buyer): Collection
    {
        return $this->purchaseOrders($agent, $buyer)
            ->join('organisations', 'organisations.id', '=', 'purchase_orders.organisation_id')
            ->leftJoin('org_suppliers', function ($join) {
                $join->on('org_suppliers.id', '=', 'purchase_orders.parent_id')->where('purchase_orders.parent_type', 'OrgSupplier');
            })
            ->whereIn('purchase_orders.state', [PurchaseOrderStateEnum::IN_PROCESS->value, PurchaseOrderStateEnum::SUBMITTED->value, PurchaseOrderStateEnum::CONFIRMED->value])
            ->selectRaw(
                'purchase_orders.id, purchase_orders.slug, purchase_orders.reference, purchase_orders.parent_name, purchase_orders.state,
                purchase_orders.submitted_at, purchase_orders.estimated_received_at, organisations.code as buyer_code, org_suppliers.slug as org_supplier_slug,
                '.PurchaseOrderStageEnum::progressSql().' as progress,
                '.PurchaseOrderStageEnum::pastEtaSql().' as past_eta,
                '.PurchaseOrderStageEnum::unconfirmedSql(self::UNCONFIRMED_DAYS).' as unconfirmed,
                floor(extract(epoch from now() - purchase_orders.submitted_at) / 86400) as days_open,
                '.$this->amountSql()
            )
            ->get();
    }

    /**
     * Orders sent in the last two years, with the day the first of their containers reached the warehouse.
     */
    private function getRecentPurchaseOrders(Agent $agent, ?Organisation $buyer): Collection
    {
        return $this->purchaseOrders($agent, $buyer)
            ->where('purchase_orders.state', '!=', PurchaseOrderStateEnum::CANCELLED->value)
            ->where('purchase_orders.submitted_at', '>', now()->subMonths(self::PERFORMANCE_MONTHS))
            ->selectRaw(
                "purchase_orders.supplier_id, purchase_orders.parent_name, purchase_orders.submitted_at, purchase_orders.estimated_received_at, purchase_orders.confirmed_at,
                purchase_orders.deposit_paid_at, purchase_orders.production_started_at, purchase_orders.qc_passed_at, purchase_orders.handed_over_at,
                (select min(stock_deliveries.received_at) from purchase_order_stock_delivery
                    join stock_deliveries on stock_deliveries.id = purchase_order_stock_delivery.stock_delivery_id
                    where purchase_order_stock_delivery.purchase_order_id = purchase_orders.id
                        and stock_deliveries.deleted_at is null and stock_deliveries.state <> 'cancelled') as received_at,
                ".$this->amountSql()
            )
            ->get();
    }

    /**
     * Delivered lines checked at the warehouse in the last two years, per supplier. A China container is booked
     * against the agent, so the supplier comes from the product on the line.
     */
    private function getQuantityChecks(Agent $agent, ?Organisation $buyer): Collection
    {
        return DB::table('stock_delivery_items')
            ->join('stock_deliveries', 'stock_deliveries.id', '=', 'stock_delivery_items.stock_delivery_id')
            ->join('supplier_products', 'supplier_products.id', '=', 'stock_delivery_items.supplier_product_id')
            ->join('suppliers', 'suppliers.id', '=', 'supplier_products.supplier_id')
            ->where('suppliers.agent_id', $agent->id)
            ->whereNull('stock_delivery_items.deleted_at')
            ->whereNull('stock_deliveries.deleted_at')
            ->whereIn('stock_delivery_items.state', ['checked', 'placed'])
            ->where('stock_deliveries.date', '>', now()->subMonths(self::PERFORMANCE_MONTHS))
            ->when($buyer, fn (Builder $query) => $query->where('stock_delivery_items.organisation_id', $buyer->id))
            ->selectRaw(
                'supplier_products.supplier_id, count(*) as lines,
                count(*) filter (where stock_delivery_items.unit_quantity_checked <> stock_delivery_items.unit_quantity) as wrong,
                count(*) filter (where stock_delivery_items.unit_quantity_checked < stock_delivery_items.unit_quantity) as short,
                count(*) filter (where stock_delivery_items.unit_quantity_checked > stock_delivery_items.unit_quantity) as over'
            )
            ->groupBy('supplier_products.supplier_id')
            ->get();
    }

    private function getTopSpend(Collection $suppliers, Collection $pastOrders, Collection $deliveries, callable $money, callable $supplierRoute): Collection
    {
        $since = now()->subMonths(self::SPEND_MONTHS);

        return $pastOrders->groupBy('supplier_id')
            ->map(function (Collection $orders, $supplierId) use ($since, $suppliers, $deliveries, $money, $supplierRoute) {
                $lastYear = $orders->filter(fn ($order) => Carbon::parse($order->submitted_at)->gt($since));
                $received = $orders->whereNotNull('received_at');
                $withEta  = $received->whereNotNull('estimated_received_at');
                $checks   = $deliveries->get($supplierId);
                $supplier = $suppliers->get($supplierId);

                return [
                    'code'            => $supplier?->code,
                    'name'            => $supplier?->name ?? $orders->first()->parent_name,
                    'spend'           => $money($lastYear->sum('amount')),
                    'purchase_orders' => $lastYear->count(),
                    'received'        => $received->count(),
                    'on_time'         => $this->percentage($withEta->filter(fn ($order) => $this->isOnTime($order))->count(), $withEta->count()),
                    'median_days'     => $this->medianDays($received),
                    'quantity_errors' => $checks ? $this->percentage($checks->wrong, $checks->lines, 1) : null,
                    'route'           => $supplierRoute($supplier?->org_supplier_slug),
                ];
            })
            ->filter(fn ($row) => $row['purchase_orders'] > 0)
            ->sortByDesc('spend')
            ->take(self::RANKING_ROWS)
            ->values();
    }

    /**
     * Supplier records that look like the same supplier entered twice: the same name once company words are
     * dropped, or the same email. Phone numbers are left out, many suppliers share the agent's own number.
     */
    private function getLastChases(array $purchaseOrderIds): Collection
    {
        return DB::table('purchase_order_chases')
            ->leftJoin('users', 'users.id', '=', 'purchase_order_chases.user_id')
            ->whereIn('purchase_order_chases.purchase_order_id', $purchaseOrderIds)
            ->selectRaw('distinct on (purchase_order_chases.purchase_order_id) purchase_order_chases.purchase_order_id, purchase_order_chases.chased_at,
                purchase_order_chases.channel, purchase_order_chases.promised_at, purchase_order_chases.note, coalesce(users.contact_name, users.username) as by')
            ->orderBy('purchase_order_chases.purchase_order_id')
            ->orderByDesc('purchase_order_chases.chased_at')
            ->get()
            ->mapWithKeys(fn ($chase) => [$chase->purchase_order_id => [
                'chased_at'   => $chase->chased_at,
                'channel'     => PurchaseOrderChaseChannelEnum::labels()[$chase->channel] ?? $chase->channel,
                'promised_at' => $chase->promised_at,
                'note'        => $chase->note,
                'by'          => $chase->by,
            ]]);
    }

    /**
     * Suggestions waiting for the directors, and those decided in the last 90 days.
     */
    private function getChangeRequests(Agent $agent, callable $supplierRoute): Collection
    {
        return SupplierChangeRequest::where('agent_id', $agent->id)
            ->where(fn ($query) => $query->where('state', SupplierChangeRequestStateEnum::PENDING)->orWhere('decided_at', '>', now()->subDays(90)))
            ->with(['supplier.orgSuppliers:id,supplier_id,slug', 'targetSupplier.orgSuppliers:id,supplier_id,slug', 'requestedBy:id,contact_name,username', 'decidedBy:id,contact_name,username'])
            ->orderByRaw("state = 'pending' desc")
            ->latest()
            ->get()
            ->map(fn (SupplierChangeRequest $request) => [
                'id'            => $request->id,
                'type'          => $request->type->value,
                'type_label'    => SupplierChangeRequestTypeEnum::labels()[$request->type->value],
                'state'         => $request->state->value,
                'state_label'   => SupplierChangeRequestStateEnum::labels()[$request->state->value],
                'supplier'      => ['code' => $request->supplier->code, 'name' => $request->supplier->name, 'route' => $supplierRoute($request->supplier->orgSuppliers->sortBy('id')->first()?->slug)],
                'target'        => $request->targetSupplier ? ['code' => $request->targetSupplier->code, 'name' => $request->targetSupplier->name, 'route' => $supplierRoute($request->targetSupplier->orgSuppliers->sortBy('id')->first()?->slug)] : null,
                'reason'        => $request->reason,
                'requested_by'  => $request->requestedBy?->contact_name ?: $request->requestedBy?->username,
                'requested_at'  => $request->created_at,
                'decided_by'    => $request->decidedBy?->contact_name ?: $request->decidedBy?->username,
                'decided_at'    => $request->decided_at,
                'decision_note' => $request->decision_note,
                'decide_route'  => ['name' => 'grp.models.supplier_change_request.decide', 'parameters' => ['supplierChangeRequest' => $request->id]],
            ]);
    }

    private function getPossibleDuplicates(Collection $suppliers, callable $supplierRoute, Collection $pending): Collection
    {
        $noise = '/\b(co|ltd|limited|company|factory|trading|industrial|industry|import|export|imp|exp|manufactory|manufacturer|group|corp|corporation|inc|llc|the|and|of)\b/';

        $byName  = $suppliers->groupBy(fn ($supplier) => 'name:'.preg_replace('/[^a-z0-9]/', '', preg_replace($noise, '', Str::lower($supplier->name ?? ''))));
        $byEmail = $suppliers->filter(fn ($supplier) => trim((string) $supplier->email) !== '')->groupBy(fn ($supplier) => 'email:'.Str::lower(trim($supplier->email)));

        return $byName->merge($byEmail)
            ->filter(fn (Collection $group, string $key) => $group->count() > 1 && !in_array($key, ['name:', 'email:']))
            ->map(fn (Collection $group, string $key) => [
                'match'     => Str::before($key, ':'),
                'suppliers' => $group->map(fn ($supplier) => [
                    'id'              => $supplier->id,
                    'pending'         => $pending->get($supplier->id),
                    'code'            => $supplier->code,
                    'name'            => $supplier->name,
                    'last_ordered_at' => $supplier->last_ordered_at,
                    'route'           => $supplierRoute($supplier->org_supplier_slug),
                ])->values(),
            ])
            ->unique(fn ($group) => $group['suppliers']->pluck('code')->sort()->implode(','))
            ->values();
    }

    private function getCompleteness(Collection $orders): array
    {
        return collect([
            'confirmed_at'          => __('Confirmed'),
            'estimated_received_at' => __('Expected date (ETA)'),
            'deposit_paid_at'       => __('Deposit paid'),
            'production_started_at' => __('Production started'),
            'qc_passed_at'          => __('QC approved'),
            'handed_over_at'        => __('Handed over'),
            'received_at'           => __('Received at warehouse'),
        ])->map(fn ($label, $field) => [
            'key'        => $field,
            'label'      => $label,
            'filled'     => $orders->whereNotNull($field)->count(),
            'total'      => $orders->count(),
            'percentage' => $this->percentage($orders->whereNotNull($field)->count(), $orders->count()),
        ])->values()->all();
    }

    private function isOnTime(object $order): bool
    {
        return Carbon::parse($order->received_at)->lte(Carbon::parse($order->estimated_received_at)->addDays(self::ON_TIME_GRACE_DAYS));
    }

    private function medianDays(Collection $receivedOrders): ?int
    {
        $median = $receivedOrders->map(fn ($order) => Carbon::parse($order->submitted_at)->diffInSeconds(Carbon::parse($order->received_at)) / 86400)->median();

        return $median === null ? null : (int) round($median);
    }

    private function isDormant(object $supplier): bool
    {
        return $supplier->last_ordered_at === null || Carbon::parse($supplier->last_ordered_at)->lt(now()->subMonths(self::DORMANT_MONTHS));
    }

    private function dormantBucket(object $supplier): string
    {
        if ($supplier->last_ordered_at === null) {
            return 'never';
        }

        $months = Carbon::parse($supplier->last_ordered_at)->diffInMonths(now());

        return match (true) {
            $months < 36 => '24_36',
            $months < 48 => '36_48',
            default      => '48',
        };
    }

    private function percentage(int|float $part, int|float $total, int $precision = 0): ?float
    {
        return $total ? round($part / $total * 100, $precision) : null;
    }
}
