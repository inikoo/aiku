<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 19 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use App\Actions\OrgAction;
use App\Actions\Traits\Dashboards\WithMarketingPeriod;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

class GetShopVisitorChannelConversions extends OrgAction
{
    use WithMarketingPeriod;

    public const int LIMIT = 300;

    public const array KINDS = ['registrations', 'orders', 'revenue', 'pending'];

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("marketing.{$this->shop->id}.view");
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(Organisation $organisation, Shop $shop, string $channel, string $kind, ActionRequest $request): array
    {
        abort_unless(in_array($kind, self::KINDS, true), 404);

        $this->initialisationFromShop($shop, $request);
        $this->setMarketingPeriod($request->user()->settings);

        return $this->handle($shop, $channel, $kind, $this->periodFrom, $this->periodTo);
    }

    /**
     * The conversions the channel was credited with in the period, biggest credit first, each with
     * the share of it the channel earned and the model that split it.
     *
     * @return array{kind: string, channel: string, total: int, truncated: bool, rows: array<int, array<string, mixed>>}
     */
    public function handle(Shop $shop, string $channel, string $kind, ?Carbon $from, ?Carbon $to): array
    {
        $context = GetShopVisitorChannelPerformance::make()->credited($shop, $from, $to);

        $credited = collect($context['conversions'] ?? [])
            ->filter(fn (array $conversion) => $conversion['kind'] === $kind && isset($conversion['credit'][$channel]))
            ->sortByDesc(fn (array $conversion) => [$conversion['credit'][$channel] * ($conversion['amount'] ?: 1), $conversion['at']])
            ->values();

        $page    = $credited->take(self::LIMIT);
        $details = $this->details($kind, $page->pluck('id')->all());
        $names   = $this->customers($page->pluck('customer_id')->unique()->all());

        return [
            'kind'      => $kind,
            'channel'   => $channel,
            'total'     => $credited->count(),
            'truncated' => $credited->count() > self::LIMIT,
            'rows'      => $page->map(fn (array $conversion) => $this->row($shop, $conversion, $channel, $details[$conversion['id']] ?? null, $names[$conversion['customer_id']] ?? null))->all(),
        ];
    }

    /**
     * @param array<string, mixed>      $conversion
     * @param object|null               $detail
     * @param object|null               $customer
     *
     * @return array<string, mixed>
     */
    private function row(Shop $shop, array $conversion, string $channel, ?object $detail, ?object $customer): array
    {
        $share = $conversion['credit'][$channel];

        return [
            'reference'       => $detail->reference ?? null,
            'route'           => $this->documentRoute($shop, $conversion['kind'], $detail),
            'customer'        => $customer?->name,
            'customer_route'  => $customer ? [
                'name'       => 'grp.org.shops.show.crm.customers.show',
                'parameters' => ['organisation' => $shop->organisation->slug, 'shop' => $shop->slug, 'customer' => $customer->slug],
            ] : null,
            'at'              => Carbon::createFromTimestamp($conversion['at'])->toIso8601String(),
            'amount'          => round($conversion['amount'], 2),
            'share'           => round($share * 100, 1),
            'credited_amount' => round($conversion['amount'] * $share, 2),
            'model'           => $conversion['model'],
        ];
    }

    /**
     * @return array{name: string, parameters: array<string, string>}|null
     */
    private function documentRoute(Shop $shop, string $kind, ?object $detail): ?array
    {
        if (! $detail || $kind === 'registrations') {
            return null;
        }

        if ($kind === 'revenue') {
            return [
                'name'       => 'grp.org.accounting.invoices.show',
                'parameters' => ['organisation' => $shop->organisation->slug, 'invoice' => $detail->slug],
            ];
        }

        return [
            'name'       => 'grp.org.shops.show.ordering.orders.show',
            'parameters' => ['organisation' => $shop->organisation->slug, 'shop' => $shop->slug, 'order' => $detail->slug],
        ];
    }

    /**
     * @param array<int, int> $ids
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function details(string $kind, array $ids)
    {
        $table = match ($kind) {
            'revenue'       => 'invoices',
            'registrations' => 'customers',
            default         => 'orders',
        };

        return DB::table($table)->whereIn('id', $ids)->get(['id', 'slug', 'reference'])->keyBy('id');
    }

    /**
     * @param array<int, int> $ids
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function customers(array $ids)
    {
        return DB::table('customers')->whereIn('id', $ids)->get(['id', 'slug', 'name'])->keyBy('id');
    }
}
