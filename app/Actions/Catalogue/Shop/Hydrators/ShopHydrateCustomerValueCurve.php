<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Thu, 22 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\CRM\TrafficSource\Attribution\GetOrderGrossMargins;
use App\Actions\CRM\TrafficSource\Attribution\GetShopOrderAttribution;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * How the gross margin of the shop's buyers grows after their first order, used to project the
 * lifetime value of the buyers the marketing dashboard has only seen for a few weeks. For each
 * point (days after the first order) it takes the buyers whose first order was in the two years
 * before that point could be reached, and stores their margin at that point over their margin at
 * each early base (7 to 90 days). A buyer of 40 days with 120 margin in their first 30 days is
 * projected at 120 × the 30-day ratio of the 1095-day point for three years.
 */
class ShopHydrateCustomerValueCurve implements ShouldBeUnique
{
    use AsAction;

    public const array BASE_DAYS = [7, 14, 30, 60, 90];

    public const array POINT_DAYS = [7, 14, 30, 60, 90, 180, 365, 730, 1095];

    public const array HORIZONS = [12 => 365, 24 => 730, 36 => 1095];

    public const int COHORT_DAYS = 730;

    private const int DAY = 86400;

    public string $jobQueue = 'hydrators-slave-low-priority';

    public int $jobTries = 3;

    public string $commandSignature = 'hydrate:shop-customer-value-curve {shop? : shop slug}';

    public function getJobUniqueId(Shop $shop): string
    {
        return (string) $shop->id;
    }

    public function handle(Shop $shop): void
    {
        $shop->crmStats()->update([
            'customer_value_curve'            => json_encode($this->curve($shop)),
            'customer_value_curve_hydrated_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function curve(Shop $shop): array
    {
        $now    = now()->getTimestamp();
        $oldest = Carbon::createFromTimestamp($now - (max(self::POINT_DAYS) + self::COHORT_DAYS) * self::DAY);

        $lastYearOrders = $this->orders($shop)
            ->whereRaw(GetShopOrderAttribution::ORDER_PLACED_AT.' >= ?', [now()->subYear()])
            ->select('orders.id');

        $margin    = GetOrderGrossMargins::averageMargin($shop, $lastYearOrders);
        $customers = $this->customers($shop, $oldest, $margin['average_margin'] ?? 0.0);

        return [
            'currency_code'  => $shop->currency->code,
            'average_margin' => $margin['average_margin'],
            'cost_coverage'  => $margin['cost_coverage'],
            'cohort_days'    => self::COHORT_DAYS,
            'base_days'      => self::BASE_DAYS,
            'points'         => array_map(fn (int $days) => $this->point($customers, $days, $now), self::POINT_DAYS),
        ];
    }

    /**
     * @param  Collection<int, array<string, float>>  $customers
     *
     * @return array{days: int, cohort_from: string, cohort_to: string, buyers: int, margin_per_buyer: float|null, ratios: array<int, float|null>}
     */
    private function point(Collection $customers, int $days, int $now): array
    {
        $cohortTo   = $now - $days * self::DAY;
        $cohortFrom = $cohortTo - self::COHORT_DAYS * self::DAY;
        $cohort     = $customers->filter(fn (array $customer) => $customer['first_at'] >= $cohortFrom && $customer['first_at'] < $cohortTo);
        $margin     = $cohort->sum("m$days");
        $ratios     = [];

        foreach (self::BASE_DAYS as $base) {
            if ($base < $days) {
                $baseMargin     = $cohort->sum("m$base");
                $ratios[$base] = $baseMargin > 0 ? round($margin / $baseMargin, 4) : null;
            }
        }

        return [
            'days'             => $days,
            'cohort_from'      => Carbon::createFromTimestamp($cohortFrom)->toDateString(),
            'cohort_to'        => Carbon::createFromTimestamp($cohortTo)->toDateString(),
            'buyers'           => $cohort->count(),
            'margin_per_buyer' => $cohort->isNotEmpty() ? round($margin / $cohort->count(), 2) : null,
            'ratios'           => $ratios,
        ];
    }

    /**
     * Every buyer whose first order is after $oldest, with their gross margin within each point
     * after that first order.
     *
     * @return Collection<int, array<string, float>>
     */
    private function customers(Shop $shop, Carbon $oldest, float $averageMargin): Collection
    {
        $firstOrders = $this->orders($shop)
            ->whereNotNull('orders.customer_id')
            ->groupBy('orders.customer_id')
            ->havingRaw('MIN('.GetShopOrderAttribution::ORDER_PLACED_AT.') >= ?', [$oldest])
            ->select('orders.customer_id')
            ->selectRaw('MIN('.GetShopOrderAttribution::ORDER_PLACED_AT.') AS first_at');

        $cohortOrders = $this->orders($shop)
            ->joinSub($firstOrders, 'first_orders', 'first_orders.customer_id', '=', 'orders.customer_id')
            ->whereRaw(GetShopOrderAttribution::ORDER_PLACED_AT.' < first_orders.first_at + make_interval(days => ?)', [max(self::POINT_DAYS)])
            ->select('orders.id', 'orders.customer_id', 'first_orders.first_at')
            ->selectRaw('EXTRACT(EPOCH FROM '.GetShopOrderAttribution::ORDER_PLACED_AT.' - first_orders.first_at) / 86400 AS age');

        $margins = GetOrderGrossMargins::lines($shop)
            ->whereIn('transactions.order_id', DB::query()->fromSub($cohortOrders, 'cohort_order_ids')->select('cohort_order_ids.id'));

        $query = DB::query()
            ->fromSub($cohortOrders, 'cohort_orders')
            ->leftJoinSub($margins, 'margins', 'margins.order_id', '=', 'cohort_orders.id')
            ->groupBy('cohort_orders.customer_id', 'cohort_orders.first_at')
            ->select('cohort_orders.customer_id')
            ->selectRaw('EXTRACT(EPOCH FROM cohort_orders.first_at) AS first_at');

        foreach (self::POINT_DAYS as $days) {
            $query->selectRaw("COALESCE(SUM(margins.costed_margin) FILTER (WHERE cohort_orders.age < $days), 0) AS costed_$days")
                ->selectRaw("COALESCE(SUM(margins.uncosted_revenue) FILTER (WHERE cohort_orders.age < $days), 0) AS uncosted_$days");
        }

        return $query->get()->map(function (object $row) use ($averageMargin) {
            $customer = ['first_at' => (float) $row->first_at];

            foreach (self::POINT_DAYS as $days) {
                $customer["m$days"] = (float) $row->{"costed_$days"} + (float) $row->{"uncosted_$days"} * $averageMargin;
            }

            return $customer;
        });
    }

    private function orders(Shop $shop): Builder
    {
        return DB::table('orders')
            ->where('orders.shop_id', $shop->id)
            ->whereNotIn('orders.state', [OrderStateEnum::CREATING->value, OrderStateEnum::CANCELLED->value])
            ->whereNull('orders.deleted_at');
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $shops = Shop::query()
            ->when($command->argument('shop'), fn ($query, $slug) => $query->where('slug', $slug))
            ->where('state', '!=', ShopStateEnum::CLOSED)
            ->get();

        foreach ($shops as $shop) {
            self::dispatch($shop);
        }

        $command->info("Queued {$shops->count()} shops");

        return 0;
    }
}
